import { store, ui, ROOM_TO_CR } from "./state.js";
import { getDashboardData, PlcAPI } from "../services/dashboardData.js";

let cameraStream = null;
let isRefreshingRoom = false;
let isRefreshingDashboard = false;
let plcEchoChannelName = null;

/** ACK alarm demo (VITE_DASHBOARD_USE_DUMMY) — hanya di memori; hilang setelah refresh halaman. */
const dummyAlarmAcks = new Map();

function realtimeEnabled() {
    return Boolean(import.meta.env.VITE_PUSHER_APP_KEY && typeof window.Echo !== "undefined");
}

function pollIntervalRoomMs() {
    return realtimeEnabled() ? 12000 : 3000;
}

function pollIntervalGlobalMs() {
    return realtimeEnabled() ? 25000 : 4000;
}

function stopPlcEcho() {
    if (window.Echo && plcEchoChannelName && typeof window.Echo.leave === "function") {
        try {
            window.Echo.leave(plcEchoChannelName);
        } catch {
            /* ignore */
        }
    }
    plcEchoChannelName = null;
}

function findRoomByApiId(apiRoomId) {
    for (const d of Object.values(store.data)) {
        const r = d.rooms.find((x) => x.api_room_id === apiRoomId);
        if (r) return r;
    }
    return null;
}

function canShowPlcSettingsLink(user) {
    return Boolean(user && user.role === "admin");
}

function renderPlcStatusBadge(status) {
    const st = status || "offline";
    const isOnline = st === "online";
    const isError = st === "error";
    const label = isOnline ? "PLC connected" : isError ? "PLC error" : "PLC offline";
    const mod = isOnline ? "rp-plc-badge--on" : isError ? "rp-plc-badge--err" : "rp-plc-badge--off";
    const icon = isOnline ? "ti-plug-connected" : isError ? "ti-alert-triangle" : "ti-plug-off";
    return `<span class="rp-plc-badge ${mod}" role="status"><i class="ti ${icon}" aria-hidden="true"></i><span>${label}</span></span>`;
}

function updateRoomPlcStrip(room) {
    const el = document.getElementById("rp-plc-strip");
    if (!el) return;
    const u = typeof window.__SCADA_USER__ !== "undefined" ? window.__SCADA_USER__ : null;
    if (u && u.role !== "viewer" && room?.api_room_id) {
        el.classList.remove("is-hidden");
        const st = room.plc_link_status || "offline";
        const badge = renderPlcStatusBadge(st);
        const settingsLink = canShowPlcSettingsLink(u)
            ? `<a class="rp-plc-strip__link" href="/settings/plc?testing_room_id=${room.api_room_id}"><i class="ti ti-settings"></i> Settings</a><span class="rp-plc-strip__sep" aria-hidden="true">·</span>`
            : "";
        el.innerHTML = `${settingsLink}${badge}`;
    } else {
        el.classList.add("is-hidden");
        el.innerHTML = "";
    }
}

function bindPlcEchoForRoom(room) {
    stopPlcEcho();
    if (!realtimeEnabled() || !room?.api_room_id) return;

    const ch = `plc.room.${room.api_room_id}`;
    plcEchoChannelName = ch;
    const plcEchoChannel = window.Echo.channel(ch);
    plcEchoChannel.listen(".PlcRoomUpdated", (payload) => {
        const rid = payload?.room_id;
        if (!rid) return;

        const target = findRoomByApiId(rid) || (ui.curRoomId ? findRoom(ui.curRoomId) : null);
        if (!target) return;

        syncRoomPlcLinkFromPollPayload(target, payload);

        const snap = payload?.data;
        const pollOk = (payload?.status || "") === "success";
        if (!pollOk || !snap || typeof snap !== "object") clearLiveSnapshotFields(target);
        else applySnapshotToRoom(target, snap);

        buildSidebarRooms();
        updateAlarmSidebar();

        if (ui.panelMode === "room" && ui.curRoomId === target.id) {
            const crId = findCR(target.id);
            if (crId) renderRoomPanelPanes(target, crId);
        }
    });
}

export function findCR(id) {
    if (store.data[id]) return id;
    return ROOM_TO_CR[id] || null;
}

export function findRoom(roomId) {
    for (const d of Object.values(store.data)) {
        const r = d.rooms.find((x) => x.id === roomId);
        if (r) return r;
    }
    return null;
}

/** true = sedang pengujian aktif (tekanan/proses uji berjalan) */
export function isRoomActivelyTesting(r) {
    return ["RUNNING", "HOLDING", "PRESSURIZING"].includes(r.phase);
}

function useDummyDashboard() {
    return import.meta.env.VITE_DASHBOARD_USE_DUMMY === "true";
}

/** Realtime: alarm register hanya dipercaya saat PLC online (sama seperti EMERGENCY). */
function plcRegistersTrusted(r) {
    return useDummyDashboard() || (r?.plc_link_status || "") === "online";
}

/**
 * #40001 tekanan masuk sebelum testing: alarm bila ada tekanan (nilai > 0, berapapun besarnya)
 * saat testing belum dimulai. Dummy/API boleh set `alarm_pressure_in` langsung.
 */
export function hasInletPressureAlarm(r) {
    if (!r) return false;
    if (useDummyDashboard() && dummyAlarmAcks.get(r.id)?.has("pressure_in")) return false;
    if (r.alarm_pressure_in === true) return plcRegistersTrusted(r);
    const pres = Number(r.pres_in ?? 0);
    return plcRegistersTrusted(r) && pres > 0 && !isRoomActivelyTesting(r);
}

function sidebarRoomDotClass(r) {
    if ((r.mode || "") === "MAINTENANCE") return "room-dot-st--mt";
    if (
        r.alarm_emergency ||
        hasInletPressureAlarm(r) ||
        r.alarm_motor ||
        r.alarm_left_motor ||
        r.alarm_right_motor
    )
        return "room-dot-st--wa";
    if ((r.plc_link_status || "") === "online") return "room-dot-st--on";
    return "room-dot-st--off";
}

/**
 * Sidebar: dua badge — kiri Running/Idle (#40009), kanan Auto/Maint (#40003).
 * Lebih jelas daripada satu badge berprioritas (MAINT menutupi TEST, dll.).
 */
function sidebarModeBadges(r) {
    const testBadge = isRoomActivelyTesting(r)
        ? `<span class="badge-sm badge-run" title="Test status: running">Run</span>`
        : `<span class="badge-sm badge-idle" title="Test status: idle">Idle</span>`;
    const modeBadge =
        r.mode === "MAINTENANCE"
            ? `<span class="badge-sm badge-maint" title="Panel mode: maintenance">Maint</span>`
            : `<span class="badge-sm badge-auto" title="Panel mode: auto">Auto</span>`;
    return `<span class="room-badges">${testBadge}${modeBadge}</span>`;
}

function fmtRegBar(n) {
    if (n == null || Number.isNaN(Number(n))) return "—";
    return Number(n).toFixed(1);
}

/** #40011 / #40012 — tampilan satuan PSI (nilai dari register, tanpa badge Normal/High). */
function fmtPressurePsi(value) {
    if (value == null || Number.isNaN(Number(value))) return "—";
    return Number(value).toFixed(1);
}

/** Label panel Operational status — selaras phase RUNNING, bukan "standby". */
function operationalTestStatusLabel(r) {
    return isRoomActivelyTesting(r) ? "Running" : "Idle";
}

/** OPEN | CLOSE saja (legacy STANDBY dinormalisasi). */
function normalizedRoofState(r) {
    if (r.reg_roof_open) return "OPEN";
    if (r.reg_roof_closed) return "CLOSE";
    if (r.roof_moving_open) return "OPEN";
    if (r.roof_moving_close) return "CLOSE";
    const rs = String(r.roof_state || "CLOSE").toUpperCase();
    return rs === "OPEN" ? "OPEN" : "CLOSE";
}

function renderRoofPositionHtml(r) {
    const open = normalizedRoofState(r) === "OPEN";
    return `
  <div class="rp-overview-block rp-overview-block--roof">
    <div class="section-label">Roof position</div>
    <div class="roof-segment" role="status" aria-label="Roof ${open ? "open" : "closed"}">
      <div class="roof-segment__cell${open ? " is-active is-open" : ""}">
        <i class="ti ti-chevrons-up" aria-hidden="true"></i>
        <span>OPEN</span>
      </div>
      <div class="roof-segment__cell${open ? "" : " is-active is-close"}">
        <i class="ti ti-chevrons-down" aria-hidden="true"></i>
        <span>CLOSE</span>
      </div>
    </div>
  </div>`;
}

export function renderRoomPanelPanes(r, crId) {
    if (!r) return;
    renderPaneOverview(r, crId);
    renderPaneLog(r);
}

function syncCameraWideClass() {
    const rp = document.getElementById("rpanel");
    if (!rp || !rp.classList.contains("open")) return;
    const onOverview =
        document.getElementById("tab-o") && document.getElementById("tab-o").classList.contains("on");
    const wide = ui.cameraWide && onOverview;
    rp.classList.toggle("dash-rpanel--camera-wide", wide);
}

export function toggleCameraWide() {
    ui.cameraWide = !ui.cameraWide;
    syncCameraWideClass();
    const btn = document.getElementById("btn-cam-wide");
    if (btn) {
        btn.setAttribute("aria-pressed", ui.cameraWide ? "true" : "false");
        btn.classList.toggle("is-active", ui.cameraWide);
        const ico = btn.querySelector("i");
        if (ico) {
            ico.className = ui.cameraWide ? "ti ti-arrows-minimize" : "ti ti-arrows-maximize";
        }
    }
}

function renderPaneOverview(r, crId) {
    const u = typeof window.__SCADA_USER__ !== "undefined" ? window.__SCADA_USER__ : null;
    const plcLink =
        canShowPlcSettingsLink(u) && r.api_room_id
            ? `<a class="rp-plc-strip__link rp-plc-strip__link--pane" href="/settings/plc?testing_room_id=${r.api_room_id}"><i class="ti ti-settings"></i> Settings</a>`
            : "";
    const wideOn = ui.cameraWide ? "true" : "false";
    const wideIco = ui.cameraWide ? "ti ti-arrows-minimize" : "ti ti-arrows-maximize";
    const p1 = Number(r.pres_bbm ?? r.test_pressure ?? 0);
    const p2 = Number(r.pres_2 ?? 0);
    const maint = r.mode === "MAINTENANCE";
    const testStatusLabel = operationalTestStatusLabel(r);
    const testActive = isRoomActivelyTesting(r);
    const locked = r.door_lock === "locked";
    /** #40002 alarm_alert — ON/OFF (register bool), bukan label ALARM/Normal. */
    const emergOn = plcRegistersTrusted(r) && Boolean(r.alarm_emergency);

    document.getElementById("pane-o").innerHTML = `
  ${plcLink}
<div class="cam-toolbar-v2">
  <div class="section-label cam-toolbar-v2__title">Kamera</div>

  <button
    type="button"
    class="btn-cam-wide${ui.cameraWide ? " is-active" : ""}"
    id="btn-cam-wide"
    onclick="toggleCameraWide()"
    aria-pressed="${wideOn}"
    title="Use map area for larger video (e.g. computer vision)">

    <i class="${wideIco}" aria-hidden="true"></i>
    Wide view
  </button>
</div>

<div class="cam-grid-v2">

  <!-- Kamera 1 -->
  <div class="cam-v2">
    <img
      src="http://192.168.1.100:5000/camera1"
      class="cam-stream"
      alt="Kamera 1">

    <span class="cam-v2-lbl">Kamera 1</span>

    <span class="cam-live">
      <span class="cam-live-dot"></span>
      LIVE
    </span>
  </div>

  <!-- Kamera 2 (sementara menggunakan stream yang sama) -->
  <div class="cam-v2">
    <img
      src="http://192.168.1.100:5000/camera1"
      class="cam-stream"
      alt="Kamera 2">

    <span class="cam-v2-lbl">Kamera 2</span>

    <span class="cam-live">
      <span class="cam-live-dot"></span>
      LIVE
    </span>
  </div>

</div>
</div>
  <div class="rp-overview-block">
    <div class="section-label">Pressure</div>
    <div class="sensor-grid-v2">
      <div class="sensor-card-v2">
        <div class="sn">Pressure 1</div>
        <div class="sv">${fmtPressurePsi(p1)}<span class="su"> PSI</span></div>
      </div>
      <div class="sensor-card-v2">
        <div class="sn">Pressure 2</div>
        <div class="sv">${fmtPressurePsi(p2)}<span class="su"> PSI</span></div>
      </div>
    </div>
  </div>
  ${renderRoofPositionHtml(r)}
  <div class="rp-overview-block">
    <div class="section-label">Operational status</div>
    <div class="roof-card-v2">
      <div class="roof-row-v2">
        <span class="rp-field-label">Panel mode</span>
        <span class="st-badge-v2" style="${maint ? "background:#ede9fe;color:#7c3aed" : "background:#dbeafe;color:#1e40af"}">${maint ? "Maintenance" : "Auto"}</span>
      </div>
      <div class="roof-row-v2">
        <span class="rp-field-label">Test status</span>
        <span class="st-badge-v2" style="${testActive ? "background:#dcfce7;color:#15803d" : "background:#f3f4f6;color:#374151"}">${testStatusLabel}</span>
      </div>
      <div class="roof-row-v2">
        <span class="rp-field-label">Access door</span>
        <span class="st-badge-v2" style="${locked ? "background:#fee2e2;color:#b91c1c" : "background:#dcfce7;color:#15803d"}"><i class="ti ${locked ? "ti-lock" : "ti-lock-open"}"></i> ${locked ? "Locked" : "Unlocked"}</span>
      </div>
      <div class="roof-row-v2">
        <span class="rp-field-label">Emergency</span>
        <span class="st-badge-v2" style="${emergOn ? "background:#fee2e2;color:#b91c1c" : "background:#f3f4f6;color:#374151"}">${emergOn ? "ON" : "OFF"}</span>
      </div>
    </div>
  </div>
  ${renderRoomAlarmSection(r)}`;
    syncCameraWideClass();
}

function renderPaneLog(r) {
    const ev = r.events && r.events.length ? r.events : [];
    let h = `<div class="section-label">Event log</div>`;
    if (!ev.length) {
        h += `<div style="font-size:12px;color:#6b7280;padding:8px 0">No log entries yet.</div>`;
    } else {
        ev.forEach((e) => {
            let box = "info";
            let ico = "ti-info-circle";
            if (e.c === "cr") {
                box = "danger";
                ico = "ti-alert-triangle";
            } else if (e.c === "wa") {
                box = "warn";
                ico = "ti-shield";
            } else if (e.c === "ok") {
                box = "ok";
                ico = "ti-check";
            }
            h += `<div class="log-item-v2">
      <div class="log-icon-v2 ${box}"><i class="ti ${ico}"></i></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12px;color:var(--txt)">${e.m}</div>
        <div style="font-size:10px;color:#6b7280;margin-top:2px">${e.t}</div>
      </div>
    </div>`;
        });
    }
    document.getElementById("pane-e").innerHTML = h;
}

const DUMMY_ALARM_LABELS = {
    emergency: "EMERGENCY",
    pressure_in: "ALARM PRESSURE",
    left_motor: "LEFT MOTOR FAIL",
    right_motor: "RIGHT MOTOR FAIL",
    motor: "MOTOR FAIL",
};

/**
 * Acknowledge alarm di mode dummy (in-memory). Setelah refresh browser, data dummy asli kembali.
 */
export function ackDummyRoomAlarm(roomId, alarmKey) {
    if (!useDummyDashboard() || !roomId || !DUMMY_ALARM_LABELS[alarmKey]) return;

    const room = findRoom(roomId);
    if (!room) return;

    if (!dummyAlarmAcks.has(roomId)) dummyAlarmAcks.set(roomId, new Set());
    dummyAlarmAcks.get(roomId).add(alarmKey);

    switch (alarmKey) {
        case "emergency":
            room.alarm_emergency = false;
            break;
        case "pressure_in":
            room.alarm_pressure_in = false;
            break;
        case "left_motor":
            room.alarm_left_motor = false;
            break;
        case "right_motor":
            room.alarm_right_motor = false;
            break;
        case "motor":
            room.alarm_motor = false;
            break;
    }

    if (!Array.isArray(room.events)) room.events = [];
    room.events.unshift({
        t: getNow(),
        c: "ok",
        m: `${DUMMY_ALARM_LABELS[alarmKey]} acknowledged (demo)`,
    });

    updateAlarmSidebar();
    buildSidebarRooms();
    if (ui.panelMode === "room" && ui.curRoomId === roomId) {
        const crId = findCR(roomId);
        if (crId) renderRoomPanelPanes(room, crId);
    }
}

function renderRoomAlarmSection(r) {
    const rows = [];
    if (r.alarm_emergency && plcRegistersTrusted(r))
        rows.push({ key: "emergency", t: "EMERGENCY", sub: r.nm });
    if (hasInletPressureAlarm(r))
        rows.push({ key: "pressure_in", t: "ALARM PRESSURE", sub: r.nm });
    if (r.dual_motor) {
        if (r.alarm_left_motor)
            rows.push({ key: "left_motor", t: "LEFT MOTOR FAIL", sub: r.nm });
        if (r.alarm_right_motor)
            rows.push({ key: "right_motor", t: "RIGHT MOTOR FAIL", sub: r.nm });
    } else if (r.alarm_motor) {
        rows.push({ key: "motor", t: "MOTOR FAIL", sub: r.nm });
    }
    if (!rows.length)
        return `<div class="section-label" style="margin-top:10px">Room alarms</div><div style="font-size:11px;color:#6b7280">No active alarms in this room.</div>`;
    const dummy = useDummyDashboard();
    let h = `<div class="section-label" style="margin-top:10px">Room alarms</div>`;
    rows.forEach((x) => {
        const ackBtn = dummy
            ? `<button type="button" class="btn-ack" onclick="ackDummyRoomAlarm('${r.id}','${x.key}')" title="Acknowledge (demo — reset setelah refresh halaman)">ACK</button>`
            : `<button type="button" class="btn-ack" disabled title="Acknowledge alarm PLC / layanan alarm (bukan mode dummy)">ACK</button>`;
        h += `<div class="alarm-room-v2">
    <i class="ti ti-alert-triangle" style="font-size:15px;color:var(--cr)"></i>
    <div style="flex:1"><div class="alarm-room-title">${x.t}</div><div class="alarm-room-sub">${x.sub}</div></div>
    ${ackBtn}
  </div>`;
    });
    return h;
}

export function updateCenterStatusBar() {
    /* Bottom status bar removed from layout */
}

function sortRoomsByDisplayName(rooms) {
    return [...rooms].sort((a, b) =>
        (a.nm || "").localeCompare(b.nm || "", undefined, { numeric: true, sensitivity: "base" }),
    );
}

function roomSidebarRowHtml(r, crId) {
    ROOM_TO_CR[r.id] = crId;
    return `<div class="room-item room-item--v2" id="ri-${r.id}" onclick="openRoomPanel('${r.id}','${crId}')">
        <div class="room-dot-st ${sidebarRoomDotClass(r)}"></div>
        <div class="room-v2-body">
          <div class="room-v2-top">
            <span class="room-v2-name">${r.nm}</span>
            ${sidebarModeBadges(r)}
          </div>
        </div>
      </div>`;
}

export function buildSidebarRooms() {
    const flatEl = document.getElementById("rooms-flat");
    if (flatEl) {
        const pairs = [];
        Object.entries(store.data).forEach(([crId, d]) => {
            (d.rooms || []).forEach((r) => pairs.push({ r, crId }));
        });
        pairs.sort((a, b) =>
            (a.r.nm || "").localeCompare(b.r.nm || "", undefined, { numeric: true, sensitivity: "base" }),
        );
        flatEl.innerHTML = pairs.map(({ r, crId }) => roomSidebarRowHtml(r, crId)).join("");
        updateCenterStatusBar();
        return;
    }

    Object.entries(store.data).forEach(([crId, d]) => {
        const cont = document.getElementById(`rooms-${crId}`);
        if (!cont) return;
        cont.innerHTML = sortRoomsByDisplayName(d.rooms || [])
            .map((r) => roomSidebarRowHtml(r, crId))
            .join("");
    });
    updateCenterStatusBar();
}

export function toggleRooms(crId) {
    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    if (!cont || !btn) return;
    const isOpen = cont.classList.contains("open");
    ["cr1", "cr2", "cr3"].forEach((k) => {
        const c = document.getElementById(`rooms-${k}`);
        const b = document.getElementById(`exp-${k}`);
        if (c) c.classList.remove("open");
        if (b) b.classList.remove("open");
    });
    const willExpand = !isOpen;
    if (willExpand) {
        cont.classList.add("open");
        btn.classList.add("open");
    }

    /** Control room tidak membuka panel kanan — hanya submenu + penanda nav */
    if (willExpand) {
        stopPlcEcho();
        updateRoomPlcStrip(null);
        stopCamera();
        if (ui.lv) {
            clearInterval(ui.lv);
            ui.lv = null;
        }
        ui.curRoomId = null;
        ui.curId = null;
        ui.panelMode = "none";
        document.getElementById("rpanel").classList.remove("open");
        document.querySelectorAll(".room-item").forEach((el) => el.classList.remove("active"));
        ["cr1", "cr2", "cr3"].forEach((k) => {
            const nav = document.getElementById(`nav-${k}`);
            if (nav) nav.classList.toggle("active", k === crId);
        });
    } else {
        ["cr1", "cr2", "cr3"].forEach((k) => {
            const nav = document.getElementById(`nav-${k}`);
            if (nav) nav.classList.remove("active");
        });
    }
}

export async function showCamera() {
    const camView = document.getElementById("camera-view");
    const video = document.getElementById("camera");
    camView.classList.add("is-visible");
    if (cameraStream) {
        video.srcObject = cameraStream;
        return;
    }
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
        });
        video.srcObject = cameraStream;
    } catch (err) {
        console.error("Camera error:", err);
        alert("Camera could not be accessed.");
    }
}

export function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach((t) => t.stop());
        cameraStream = null;
    }
    document.getElementById("camera-view").classList.remove("is-visible");
}

export function openRoomPanel(roomId, crId) {
    const r = findRoom(roomId);
    const crD = store.data[crId];
    if (!r || !crD) return;

    ui.cameraWide = false;
    ui.panelMode = "room";
    ui.curRoomId = roomId;
    ui.curId = crId;

    const rp = document.getElementById("rpanel");
    const col = r.col || "#059652";
    document.getElementById("rp-accent").style.background = col;
    rp.style.setProperty("--ra", col);

    const titleEl = document.getElementById("rp-title");
    if (titleEl) titleEl.textContent = r.nm || "—";

    updateRoomPlcStrip(r);

    renderRoomPanelPanes(r, crId);
    swTab("o");
    rp.classList.add("open");

    ["cr1", "cr2", "cr3"].forEach((k) => {
        const nav = document.getElementById(`nav-${k}`);
        if (nav) nav.classList.remove("active");
    });
    const navCr = document.getElementById(`nav-${crId}`);
    if (navCr) navCr.classList.add("active");
    document
        .querySelectorAll(".room-item")
        .forEach((el) => el.classList.remove("active"));
    const ri = document.getElementById(`ri-${roomId}`);
    if (ri) ri.classList.add("active");

    if (typeof window.__syncDemoRoofSelect === "function") {
        window.__syncDemoRoofSelect(roomId);
    }

    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    if (cont && btn) {
        cont.classList.add("open");
        btn.classList.add("open");
    }

    bindPlcEchoForRoom(r);

    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(async () => {
        if (ui.panelMode !== "room" || !ui.curRoomId) return;
        await refreshCurrentRoomFromApi();
        const crIdCur = findCR(ui.curRoomId);
        const rr = findRoom(ui.curRoomId);
        if (!rr) return;
        renderRoomPanelPanes(rr, crIdCur);
        updateAlarmSidebar();
        updateCenterStatusBar();
    }, pollIntervalRoomMs());
}

/** Hanya membuka submenu control room di sidebar (panel kanan untuk CR tidak dipakai). */
export function openPanel(id) {
    if (!store.data[id]) return;
    toggleRooms(id);
}

export function closePanel() {
    ui.cameraWide = false;
    document.getElementById("rpanel")?.classList.remove("dash-rpanel--camera-wide");
    stopPlcEcho();
    updateRoomPlcStrip(null);
    stopCamera();
    const tEl = document.getElementById("rp-title");
    if (tEl) tEl.textContent = "—";
    document.getElementById("rpanel").classList.remove("open");
    ui.curId = null;
    ui.curRoomId = null;
    ui.panelMode = "none";
    if (ui.lv) {
        clearInterval(ui.lv);
        ui.lv = null;
    }
    ["cr1", "cr2", "cr3"].forEach((k) => {
        const nav = document.getElementById(`nav-${k}`);
        if (nav) nav.classList.remove("active");
    });
    document.querySelectorAll(".room-item").forEach((el) => el.classList.remove("active"));
}

/** Control room — hanya tekanan saluran */
export function renderSensor(id) {
    const el = document.getElementById("pane-o");
    if (!el) return;
    const d = store.data[id];
    const p = d.line_pressure ?? 0;
    el.innerHTML = `
  <div class="rp-sec">Line pressure</div>
  <div class="mc-grid">
    <div class="mc P" style="grid-column:1/-1">
      <div class="mc-lbl">LINE PRESSURE (bar)</div>
      <div class="mc-val">${typeof p === "number" ? p.toFixed(2) : p}</div>
      <div class="mc-st ok">NORMAL</div>
    </div>
  </div>
  <div style="margin-top:10px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString("en-GB")}</span>
  </div>`;
}

/** Control room — satu switch door lock area */
export function renderLock(id) {
    const el = document.getElementById("pane-o");
    if (!el) return;
    const d = store.data[id];
    const locked = d.door_lock === "locked";
    el.innerHTML = `
  <div class="rp-sec">SWITCH DOOR LOCK</div>
  <div class="lk-item">
    <i class="ti ${locked ? "ti-lock" : "ti-lock-open"} lk-ico" style="color:${locked ? "var(--ok)" : "var(--cr)"}"></i>
    <div style="flex:1">
      <div class="lk-name">Control room area / test access</div>
      <div class="lk-time">Switch ${locked ? "locked" : "open"}</div>
    </div>
    <div class="lk-badge ${locked ? "lk" : "ul"}">${locked ? "LOCKED" : "OPEN"}</div>
  </div>`;
}

export function renderEvents(id) {
    const d = store.data[id];
    let h = `<div class="rp-sec">Latest log</div>`;
    d.events.forEach((e) => {
        h += `<div class="ev-row">
      <div class="ev-t">${e.t}</div>
      <div class="ev-d ${e.c}"></div>
      <div class="ev-m">${e.m}</div>
    </div>`;
    });
    document.getElementById("pane-e").innerHTML = h;
}

export function swTab(n) {
    if (n !== "o") {
        ui.cameraWide = false;
        document.getElementById("rpanel")?.classList.remove("dash-rpanel--camera-wide");
    }
    ["o", "e"].forEach((t) => {
        const tab = document.getElementById(`tab-${t}`);
        const pane = document.getElementById(`pane-${t}`);
        if (tab) tab.classList.remove("on");
        if (pane) pane.style.display = "none";
    });
    const activeTab = document.getElementById(`tab-${n}`);
    const activePane = document.getElementById(`pane-${n}`);
    if (activeTab) activeTab.classList.add("on");
    if (activePane) activePane.style.display = "block";
    syncCameraWideClass();
}

function pushAlarm(out, lv, nm, sub, crId, roomId) {
    out.push({ lv, nm, sub, crId, roomId, time: getNow() });
}

/** Motor fail (PLC / simulasi). */
export function getSidebarAlarms() {
    const out = [];
    for (const [crId, d] of Object.entries(store.data)) {
        for (const r of d.rooms) {
            const nm = `${r.nm}`;
            if (r.dual_motor) {
                if (r.alarm_left_motor)
                    pushAlarm(out, "cr", nm, "LEFT MOTOR FAIL", crId, r.id);
                if (r.alarm_right_motor)
                    pushAlarm(out, "cr", nm, "RIGHT MOTOR FAIL", crId, r.id);
            } else if (r.alarm_motor) {
                pushAlarm(out, "wa", nm, "MOTOR FAIL", crId, r.id);
            }
        }
    }
    return out;
}

/** Emergency dari holding register #40002 (`alarm_alert`). */
function getSidebarEmergencyAlerts() {
    const out = [];
    for (const [crId, d] of Object.entries(store.data)) {
        for (const r of d.rooms) {
            if ((r.plc_link_status || "") === "online" && r.alarm_emergency)
                pushAlarm(out, "cr", `${r.nm}`, "EMERGENCY", crId, r.id);
        }
    }
    return out;
}

/** #40001 — tekanan masuk sebelum testing dimulai. */
function getSidebarPressureInAlerts() {
    const out = [];
    for (const [crId, d] of Object.entries(store.data)) {
        for (const r of d.rooms) {
            if (hasInletPressureAlarm(r))
                pushAlarm(out, "wa", `${r.nm}`, "ALARM PRESSURE", crId, r.id);
        }
    }
    return out;
}

/** Satu daftar sidebar: emergency, pressure in, lalu motor. */
export function getSidebarCombinedAlerts() {
    return [
        ...getSidebarEmergencyAlerts(),
        ...getSidebarPressureInAlerts(),
        ...getSidebarAlarms(),
    ];
}

export function getNow() {
    return new Date().toLocaleTimeString("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

export function updateAlarmSidebar() {
    const combined = getSidebarCombinedAlerts();
    const count = combined.length;
    const ac = document.getElementById("al-count");
    if (ac) {
        ac.textContent = String(count);
        ac.className =
            "alarm-count " +
            (count > 0
                ? combined.some((a) => a.lv === "cr")
                    ? ""
                    : "wa"
                : "ok");
    }
    const list = document.getElementById("al-list");
    if (!count) {
        list.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>No active alarms</div>`;
    } else {
        list.innerHTML = combined
            .map(
                (a) => `
      <div class="al-row" onclick="openRoomPanel('${a.roomId}','${a.crId}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt"><div class="al-name">${a.nm}</div><div class="al-sub al-sub--type">${a.sub}</div></div>
        <div class="al-time">${a.time}</div>
      </div>`,
            )
            .join("");
    }

    updateCenterStatusBar();

    document.getElementById("hkpi-alarm").textContent = count;
    document.getElementById("hkpi-alarm-dot").style.background =
        count > 0
            ? combined.some((a) => a.lv === "cr")
                ? "var(--cr)"
                : "var(--wa)"
            : "var(--ok)";
    let active = 0;
    Object.values(store.data).forEach((d) =>
        d.rooms.forEach((r) => {
            if (r.st === "ACTIVE" || r.st === "OPERATIONAL") active++;
        }),
    );
    document.getElementById("hkpi-active").textContent = active;
}

export function updateUtilSidebar() {
    const el = document.getElementById("hkpi-pwr");
    if (el && store.util?.listrik)
        el.textContent = `${store.util.listrik.total_kw.toFixed(0)} kW`;
}

/** Dulu memuat animasi angka acak untuk mode dummy; sekarang tidak mengubah data (hindari “random” di UI). */
export function tickRoom() {}

export function tickUtil() {}

function extractSnapshotValue(snapshot, keys, fallback = null) {
    for (const key of keys) {
        const node = snapshot?.[key];
        if (node && typeof node === "object" && "value" in node) {
            return node.value;
        }
    }
    return fallback;
}

/** Hentikan bit emergency / tekanan dari snapshot cache bila tidak ada data baru dari PLC. */
function clearLiveSnapshotFields(room) {
    room.alarm_emergency = false;
    room.alarm_pressure_in = false;
    room.pres_in = 0;
    room.pres_2 = 0;
    if (room.tp === "TEST PIT") room.test_pressure = 0;
    else room.pres_bbm = 0;
}

/** Samakan dengan webhook: `success` → online; selain itu jangan percaya register di cache. */
function syncRoomPlcLinkFromPollPayload(room, payload) {
    const s = payload?.status;
    if (s === "success") room.plc_link_status = "online";
    else if (s === "error") room.plc_link_status = "error";
    else if (s === "no_data" || s === "offline" || s === "timeout" || s) room.plc_link_status = "offline";
}

function applySnapshotToRoom(room, snapshot) {
    if (!snapshot || typeof snapshot !== "object") return;

    const plcLive = (room.plc_link_status || "") === "online";

    const rawIn = Number(extractSnapshotValue(snapshot, ["40001", "tekanan_masuk", "line_pressure"], NaN));
    const processPressure = Number(extractSnapshotValue(snapshot, ["40011", "pressure_1"], NaN));
    const pres2 = Number(extractSnapshotValue(snapshot, ["40012", "pressure_2"], NaN));
    const alarmAlert = Boolean(extractSnapshotValue(snapshot, ["40002", "alarm_alert"], false));
    const maintenance = Boolean(extractSnapshotValue(snapshot, ["40003", "mode_maintenance"], false));
    const roofClosed = Boolean(extractSnapshotValue(snapshot, ["40004", "roof_tertutup"], false));
    const roofOpenBit = Boolean(extractSnapshotValue(snapshot, ["40005", "roof_terbuka"], false));
    const roofMovingOpen = Boolean(
        extractSnapshotValue(snapshot, ["40006", "roof_bergerak_buka"], false),
    );
    const roofMovingClose = Boolean(
        extractSnapshotValue(snapshot, ["40007", "roof_bergerak_tutup"], false),
    );
    const doorLocked = Boolean(
        extractSnapshotValue(snapshot, ["40008", "pintu_terkunci", "door_locked"], false),
    );
    if (!Number.isNaN(rawIn)) {
        const bar = rawIn > 50 ? rawIn / 100 : rawIn;
        room.pres_in = +bar.toFixed(2);
    }
    const testingOn = Boolean(
        extractSnapshotValue(snapshot, ["40009", "testing_dimulai", "testing_running"], false),
    );
    if (!Number.isNaN(processPressure)) {
        if (room.tp === "TEST PIT") room.test_pressure = +processPressure.toFixed(2);
        else room.pres_bbm = +processPressure.toFixed(2);
    }
    if (!Number.isNaN(pres2)) room.pres_2 = +pres2.toFixed(2);

    room.reg_roof_open = roofOpenBit;
    room.reg_roof_closed = roofClosed;
    room.roof_moving_open = roofMovingOpen;
    room.roof_moving_close = roofMovingClose;

    if (roofOpenBit) room.roof_state = "OPEN";
    else if (roofClosed) room.roof_state = "CLOSE";
    else if (roofMovingOpen) room.roof_state = "OPEN";
    else if (roofMovingClose) room.roof_state = "CLOSE";
    else room.roof_state = "CLOSE";

    /* Hanya percaya #40002 jika ruang bertanda PLC online (hindari cache lama saat putus). */
    room.alarm_emergency = plcLive && alarmAlert;
    room.mode = maintenance ? "MAINTENANCE" : "AUTO";
    room.door_lock = doorLocked ? "locked" : "unlocked";
    room.phase = testingOn ? "RUNNING" : "STANDBY";
    /* #40001: tekanan masuk sebelum testing — alarm jika ada tekanan (> 0) saat #40009 belum ON. */
    const presInBar = Number(room.pres_in ?? 0);
    room.alarm_pressure_in = plcLive && presInBar > 0 && !testingOn;
}

async function refreshCurrentRoomFromApi() {
    if (isRefreshingRoom || !ui.curRoomId) return;
    const room = findRoom(ui.curRoomId);
    if (!room || !room.api_room_id) return;

    isRefreshingRoom = true;
    try {
        const payload = await PlcAPI.getRoomData(room.api_room_id);
        if (payload && typeof payload === "object") syncRoomPlcLinkFromPollPayload(room, payload);

        if (payload?.data && typeof payload.data === "object" && payload.status === "success") {
            applySnapshotToRoom(room, payload.data);
        } else {
            clearLiveSnapshotFields(room);
        }
    } catch (err) {
        console.warn("[Realtime] room polling gagal:", err?.message || err);
        room.alarm_emergency = false;
        room.alarm_pressure_in = false;
    } finally {
        isRefreshingRoom = false;
    }
}

async function refreshDashboardFromApi() {
    if (isRefreshingDashboard) return;
    isRefreshingDashboard = true;
    try {
        const payload = await getDashboardData();
        if (payload?.controlRooms) {
            store.data = payload.controlRooms;
            Object.keys(ROOM_TO_CR).forEach((key) => delete ROOM_TO_CR[key]);
            buildSidebarRooms();
            updateUtilSidebar();
            updateCenterStatusBar();
        }
    } catch (err) {
        console.warn("[Realtime] dashboard refresh gagal:", err?.message || err);
    } finally {
        isRefreshingDashboard = false;
    }
}

export function startLiveLoop() {
    const useDummy = import.meta.env.VITE_DASHBOARD_USE_DUMMY === "true";

    setInterval(async () => {
        if (useDummy) {
            /* Dummy statis dari getDashboardData — tanpa random walk per frame. */
        } else {
            await refreshDashboardFromApi();
            if (ui.panelMode === "room") await refreshCurrentRoomFromApi();
        }

        updateAlarmSidebar();
        updateUtilSidebar();
        updateCenterStatusBar();
        if (ui.curRoomId && ui.panelMode === "room") {
            const crId = findCR(ui.curRoomId);
            const room = findRoom(ui.curRoomId);
            if (room && crId) renderRoomPanelPanes(room, crId);
        }
    }, pollIntervalGlobalMs());
}

export function toggleSidebar() {
    const sb = document.getElementById("lsb"),
        ov = document.getElementById("overlay");
    const open = sb.classList.toggle("open");
    ov.style.display = "block";
    setTimeout(() => (ov.style.opacity = open ? "1" : "0"), 10);
    if (!open) setTimeout(() => (ov.style.display = "none"), 300);
}

export function closeSidebar() {
    document.getElementById("lsb").classList.remove("open");
    const ov = document.getElementById("overlay");
    ov.style.opacity = "0";
    setTimeout(() => (ov.style.display = "none"), 300);
}

export function initClock() {
    const tick = () => {
        const n = new Date(),
            p = (v) => String(v).padStart(2, "0");
        document.getElementById("clk").textContent =
            `${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
        setTimeout(tick, 1000);
    };
    tick();
}

export function initLogoFallback() {
    const img = document.querySelector(".logo-img");
    const fb = document.querySelector(".logo-fallback");
    if (!img || !fb) return;
    img.addEventListener("error", () => {
        img.classList.add("is-broken");
        fb.classList.add("is-visible");
    });
}

export function initOptionalTabs() {}

export function registerGlobals() {
    window.toggleSidebar = toggleSidebar;
    window.closeSidebar = closeSidebar;
    window.openPanel = openPanel;
    window.openRoomPanel = openRoomPanel;
    window.closePanel = closePanel;
    window.swTab = swTab;
    window.toggleRooms = toggleRooms;
    window.toggleCameraWide = toggleCameraWide;
    window.ackDummyRoomAlarm = ackDummyRoomAlarm;
}

/* =====================================
YOLO HUMAN DETECTION ALARM
===================================== */

async function checkHumanDetection() {

    try {

        const response = await fetch(
            "http://192.168.1.100:5000/alarm_status"
        );

        const data = await response.json();

        const alarmCount =
            document.getElementById("al-count");

        const alarmList =
            document.getElementById("al-list");

        if (!alarmCount || !alarmList) return;

        if (data.person_detected) {

            alarmCount.textContent = "1";

            alarmList.innerHTML = `
                <div class="human-alarm">
                    🔴 Human detected on Camera 1
                </div>
            `;

        } else {

            alarmCount.textContent = "0";

            alarmList.innerHTML = `
                <div class="al-empty-msg">
                    No active alarms
                </div>
            `;
        }

    } catch (error) {

        console.error(
            "Alarm API Error:",
            error
        );
    }
}

/* Start Monitoring */
checkHumanDetection();

/* Check every 1 second */
setInterval(
    checkHumanDetection,
    1000
);