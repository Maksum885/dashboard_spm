import { store, ui, ROOM_TO_CR } from "./state.js";
import { getDashboardData, PlcAPI } from "../services/dashboardData.js";

let cameraStream = null;
let isRefreshingRoom = false;
let isRefreshingDashboard = false;
let plcEchoChannelName = null;

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

function updateRoomPlcStrip(room) {
    const el = document.getElementById("rp-plc-strip");
    if (!el) return;
    const u = typeof window.__SCADA_USER__ !== "undefined" ? window.__SCADA_USER__ : null;
    if (u && u.role !== "viewer" && room?.api_room_id) {
        el.classList.remove("is-hidden");
        const st = room.plc_link_status || "offline";
        const stLabel = st === "online" ? "PLC online" : "PLC offline / no data";
        el.innerHTML = `<a class="rp-plc-strip__link" href="/settings/plc?testing_room_id=${room.api_room_id}"><i class="ti ti-settings"></i> Settings <span class="rp-plc-strip__id">· Room #${room.api_room_id} · ${stLabel}</span></a>`;
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
        const snap = payload?.data;
        if (!rid || !snap || typeof snap !== "object") return;

        const target = findRoomByApiId(rid) || (ui.curRoomId ? findRoom(ui.curRoomId) : null);
        if (!target) return;

        applySnapshotToRoom(target, snap);
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

function sidebarRoomDotClass(r) {
    if ((r.mode || "") === "MAINTENANCE") return "room-dot-st--mt";
    if (r.alarm_emergency || r.alarm_left_motor || r.alarm_right_motor)
        return "room-dot-st--wa";
    if ((r.plc_link_status || "") === "online") return "room-dot-st--on";
    return "room-dot-st--off";
}

function sidebarModeBadges(r) {
    if (r.mode === "MAINTENANCE")
        return `<span class="badge-sm badge-maint">MAINT</span>`;
    if (isRoomActivelyTesting(r))
        return `<span class="badge-sm badge-test">TEST</span>`;
    return `<span class="badge-sm badge-auto">AUTO</span>`;
}

function fmtRegBar(n) {
    if (n == null || Number.isNaN(Number(n))) return "—";
    return Number(n).toFixed(1);
}

function presInBar(r) {
    const v = Number(r.pres_in ?? 0);
    return Math.min(100, Math.max(0, (v / 10) * 100));
}

function roofMotionLabel(r) {
    if (r.roof_moving_open) return { t: "Opening", cl: "st-badge-v2", st: "wa" };
    if (r.roof_moving_close)
        return { t: "Closing", cl: "st-badge-v2", st: "wa" };
    const rs = r.roof_state || "CLOSE";
    if (rs === "OPEN") return { t: "Open", cl: "st-badge-v2", st: "ok" };
    if (rs === "STANDBY") return { t: "Standby", cl: "st-badge-v2", st: "in" };
    return { t: "Closed", cl: "st-badge-v2", st: "nx" };
}

function roofAnimWidthPct(r) {
    if (r.roof_moving_open || r.roof_moving_close) return 42;
    if ((r.roof_state || "") === "OPEN") return 100;
    if ((r.roof_state || "") === "STANDBY") return 50;
    return 100;
}

function roofAnimFillClass(r) {
    if (r.roof_moving_open || r.roof_moving_close) return "";
    if ((r.roof_state || "") === "OPEN") return "is-idle";
    return "is-closed";
}

function roofPositionLabel(r) {
    if (r.reg_roof_open) return "Open";
    if (r.reg_roof_closed) return "Closed";
    return "Standby";
}

function roofPositionBadgeStyle(r) {
    if (r.reg_roof_open) return "background:#e0f2fe;color:#075985";
    if (r.reg_roof_closed) return "background:#f3f4f6;color:#374151";
    return "background:#e0f2fe;color:#075985";
}

function setRoomPanelUpdated() {
    const el = document.getElementById("rp-upd-time");
    if (el) el.textContent = new Date().toLocaleTimeString("en-GB");
}

export function renderRoomPanelPanes(r, crId) {
    if (!r) return;
    setRoomPanelUpdated();
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
        u && u.role !== "viewer" && r.api_room_id
            ? `<a class="rp-plc-strip__link" style="margin-bottom:8px;display:inline-flex" href="/settings/plc?testing_room_id=${r.api_room_id}"><i class="ti ti-settings"></i> Open settings for this room</a>`
            : "";
    const wideOn = ui.cameraWide ? "true" : "false";
    const wideIco = ui.cameraWide ? "ti ti-arrows-minimize" : "ti ti-arrows-maximize";
    const p1 = Number(r.pres_bbm ?? r.test_pressure ?? 0);
    const p2 = Number(r.pres_2 ?? 0);
    const hi = p2 > 7 || (p1 > 0 && p2 > p1 * 1.08);
    const mv = roofMotionLabel(r);
    const stStyle =
        mv.st === "wa"
            ? "background:#fef3c7;color:#92400e"
            : mv.st === "ok"
              ? "background:#dcfce7;color:#15803d"
              : "background:#e0f2fe;color:#075985";
    const maint = r.mode === "MAINTENANCE";
    const testing = isRoomActivelyTesting(r);
    const locked = r.door_lock === "locked";
    const emerg = r.alarm_emergency;

    document.getElementById("pane-o").innerHTML = `
  ${plcLink}
  <div class="cam-toolbar-v2">
    <div class="section-label cam-toolbar-v2__title">Cameras</div>
    <button type="button" class="btn-cam-wide${ui.cameraWide ? " is-active" : ""}" id="btn-cam-wide" onclick="toggleCameraWide()" aria-pressed="${wideOn}" title="Use map area for larger video (e.g. computer vision)">
      <i class="${wideIco}" aria-hidden="true"></i> Wide view
    </button>
  </div>
  <div class="cam-grid-v2">
    <div class="cam-v2"><i class="ti ti-video-off" aria-hidden="true"></i><span class="cam-v2-lbl">CAM 1 — Interior</span><span class="cam-live"><span class="cam-live-dot"></span>LIVE</span></div>
    <div class="cam-v2"><i class="ti ti-video-off" aria-hidden="true"></i><span class="cam-v2-lbl">CAM 2 — Roof</span><span class="cam-live"><span class="cam-live-dot"></span>LIVE</span></div>
  </div>
  <div class="rp-overview-block">
    <div class="section-label">Pressure</div>
    <div class="sensor-grid-v2">
      <div class="sensor-card-v2">
        <div class="sn">Pressure 1 <span class="reg-addr">#40011</span></div>
        <div class="sv">${fmtRegBar(p1)}<span class="su"> bar</span></div>
        <div class="sensor-badge-v2 sb-ok">Normal</div>
      </div>
      <div class="sensor-card-v2">
        <div class="sn">Pressure 2 <span class="reg-addr">#40012</span></div>
        <div class="sv">${fmtRegBar(p2)}<span class="su"> bar</span></div>
        <div class="sensor-badge-v2 ${hi ? "sb-warn" : "sb-ok"}">${hi ? "High" : "Normal"}</div>
      </div>
    </div>
    <div class="sensor-card-v2" style="margin-top:8px">
      <div class="sn">Inlet pressure <span class="reg-addr">#40001</span></div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div class="sv" style="font-size:18px">${fmtRegBar(r.pres_in)}<span class="su"> bar</span></div>
        <div class="pres-in-bar" style="min-width:100px;flex:1"><i style="width:${presInBar(r)}%"></i></div>
        <span style="font-size:10px;color:#6b7280">10 bar max</span>
      </div>
    </div>
  </div>
  <div class="rp-overview-block">
    <div class="section-label">Roof position</div>
    <div class="roof-card-v2">
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Current status</span>
        <span class="st-badge-v2" style="${stStyle}">${mv.t}</span>
      </div>
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Motion <span class="reg-addr">#40006/#40007</span></span>
        <div class="roof-anim-bar"><div class="roof-anim-fill ${roofAnimFillClass(r)}" style="width:${roofAnimWidthPct(r)}%"></div></div>
      </div>
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Position <span class="reg-addr">#40004/#40005</span></span>
        <span class="st-badge-v2" style="${roofPositionBadgeStyle(r)}">${roofPositionLabel(r)}</span>
      </div>
    </div>
  </div>
  <div class="rp-overview-block">
    <div class="section-label">Operational status</div>
    <div class="roof-card-v2">
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Panel mode <span class="reg-addr">#40003</span></span>
        <span class="st-badge-v2" style="${maint ? "background:#ede9fe;color:#7c3aed" : "background:#dbeafe;color:#1e40af"}">${maint ? "Maintenance" : "Auto"}</span>
      </div>
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Test status <span class="reg-addr">#40009</span></span>
        <span class="st-badge-v2" style="${testing ? "background:#dcfce7;color:#15803d" : "background:#f3f4f6;color:#374151"}">${testing ? "Testing" : "Idle / standby"}</span>
      </div>
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Access door <span class="reg-addr">#40008</span></span>
        <span class="st-badge-v2" style="${locked ? "background:#fee2e2;color:#b91c1c" : "background:#dcfce7;color:#15803d"}"><i class="ti ${locked ? "ti-lock" : "ti-lock-open"}"></i> ${locked ? "Locked" : "Unlocked"}</span>
      </div>
      <div class="roof-row-v2">
        <span style="font-size:11px;color:#6b7280">Emergency <span class="reg-addr">#40002</span></span>
        <span class="st-badge-v2" style="${emerg ? "background:#fee2e2;color:#b91c1c" : "background:#f0fdf4;color:#15803d"}">${emerg ? "ALARM" : "Normal"}</span>
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

function renderRoomAlarmSection(r) {
    const rows = [];
    if (r.alarm_left_motor)
        rows.push({
            t: "LEFT MOTOR FAIL",
            sub: r.nm,
        });
    if (r.alarm_right_motor)
        rows.push({
            t: "RIGHT MOTOR FAIL",
            sub: r.nm,
        });
    if (!rows.length)
        return `<div class="section-label" style="margin-top:10px">Room alarms</div><div style="font-size:11px;color:#6b7280">No active alarms in this room.</div>`;
    let h = `<div class="section-label" style="margin-top:10px">Room alarms</div>`;
    rows.forEach((x) => {
        h += `<div class="alarm-room-v2">
    <i class="ti ti-alert-triangle" style="font-size:15px;color:var(--cr)"></i>
    <div style="flex:1"><div style="font-size:11px;font-weight:600;color:#b91c1c">${x.t}</div><div style="font-size:10px;color:#6b7280">${x.sub}</div></div>
    <button type="button" class="btn-ack" title="Acknowledge (PLC / alarm service)">ACK</button>
  </div>`;
    });
    return h;
}

export function updateCenterStatusBar() {
    /* Bottom status bar removed from layout */
}

export function buildSidebarRooms() {
    Object.entries(store.data).forEach(([crId, d]) => {
        const cont = document.getElementById(`rooms-${crId}`);
        if (!cont) return;
        cont.innerHTML = d.rooms
            .map((r) => {
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
            })
            .join("");
    });
    updateCenterStatusBar();
}

export function toggleRooms(crId) {
    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    const isOpen = cont.classList.contains("open");
    ["cr1", "cr2", "cr3"].forEach((k) => {
        document.getElementById(`rooms-${k}`).classList.remove("open");
        document.getElementById(`exp-${k}`).classList.remove("open");
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
        ["cr1", "cr2", "cr3"].forEach((k) =>
            document.getElementById(`nav-${k}`).classList.toggle("active", k === crId),
        );
    } else {
        ["cr1", "cr2", "cr3"].forEach((k) =>
            document.getElementById(`nav-${k}`).classList.remove("active"),
        );
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

    ["cr1", "cr2", "cr3"].forEach((k) =>
        document.getElementById(`nav-${k}`).classList.remove("active"),
    );
    document.getElementById(`nav-${crId}`).classList.add("active");
    document
        .querySelectorAll(".room-item")
        .forEach((el) => el.classList.remove("active"));
    const ri = document.getElementById(`ri-${roomId}`);
    if (ri) ri.classList.add("active");

    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    cont.classList.add("open");
    btn.classList.add("open");

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
    const uEl = document.getElementById("rp-upd-time");
    if (uEl) uEl.textContent = "—";
    document.getElementById("rpanel").classList.remove("open");
    ui.curId = null;
    ui.curRoomId = null;
    ui.panelMode = "none";
    if (ui.lv) {
        clearInterval(ui.lv);
        ui.lv = null;
    }
    ["cr1", "cr2", "cr3"].forEach((k) =>
        document.getElementById(`nav-${k}`).classList.remove("active"),
    );
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

/** Alarm sidebar: hanya motor fail. Emergency dan pressure in ditampilkan di area alerts terpisah. */
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

export function getSidebarAreaAlerts() {
    const out = [];
    for (const [crId, d] of Object.entries(store.data)) {
        for (const r of d.rooms) {
            const nm = `${r.nm}`;
            if (r.alarm_emergency)
                pushAlarm(out, "cr", nm, "EMERGENCY", crId, r.id);
            if (r.alarm_pressure_in)
                pushAlarm(out, "wa", nm, "PRESSURE IN", crId, r.id);
        }
    }
    return out;
}

export function getNow() {
    return new Date().toLocaleTimeString("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

export function updateAlarmSidebar() {
    const allAl = getSidebarAlarms();
    const areaAlerts = getSidebarAreaAlerts();
    const count = allAl.length;
    const ac = document.getElementById("al-count");
    if (ac) {
        ac.textContent = String(count);
        ac.className =
            "alarm-count " +
            (count > 0 ? (allAl.some((a) => a.lv === "cr") ? "" : "wa") : "ok");
    }
    const list = document.getElementById("al-list");
    if (!count) {
        list.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>No alarms</div>`;
    } else {
        list.innerHTML = allAl
            .map(
                (a) => `
      <div class="al-row" onclick="openRoomPanel('${a.roomId}','${a.crId}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt"><div class="al-name">${a.nm}</div><div class="al-sub">${a.sub}</div></div>
        <div class="al-time">${a.time}</div>
      </div>`,
            )
            .join("");
    }

    updateCenterStatusBar();

    const areaList = document.getElementById("area-list");
    if (!areaAlerts.length) {
        areaList.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>No area alerts</div>`;
    } else {
        areaList.innerHTML = areaAlerts
            .map(
                (a) => `
      <div class="al-row" onclick="openRoomPanel('${a.roomId}','${a.crId}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt"><div class="al-name">${a.nm}</div><div class="al-sub">${a.sub}</div></div>
        <div class="al-time">${a.time}</div>
      </div>`,
            )
            .join("");
    }

    document.getElementById("hkpi-alarm").textContent = count;
    document.getElementById("hkpi-alarm-dot").style.background =
        count > 0
            ? allAl.some((a) => a.lv === "cr")
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

export function tickRoom(id) {
    const d = store.data[id];
    if (d.line_pressure != null) {
        d.line_pressure = +(
            d.line_pressure +
            (Math.random() - 0.5) * 0.04
        ).toFixed(2);
    }
    d.rooms.forEach((r) => {
        if (r.tp === "TEST CELL") {
            if (r.pres_bbm != null)
                r.pres_bbm = +(
                    r.pres_bbm +
                    (Math.random() - 0.5) * 0.06
                ).toFixed(2);
        }
        if (r.tp === "TEST PIT" && r.max_st === "RUNNING") {
            if (r.test_pressure < r.target_pressure)
                r.test_pressure = +Math.min(
                    r.target_pressure,
                    r.test_pressure + r.pressure_rate * 0.05,
                ).toFixed(1);
            r.max_outlet = r.test_pressure;
        }
    });
}

export function tickUtil() {
    if (!store.util?.pam) return;
    store.util.pam.pump1.flow = +(
        store.util.pam.pump1.flow +
        (Math.random() - 0.5) * 2
    ).toFixed(1);
    store.util.listrik.total_kw = +(
        store.util.listrik.total_kw +
        (Math.random() - 0.5) * 3
    ).toFixed(1);
}

function extractSnapshotValue(snapshot, keys, fallback = null) {
    for (const key of keys) {
        const node = snapshot?.[key];
        if (node && typeof node === "object" && "value" in node) {
            return node.value;
        }
    }
    return fallback;
}

function applySnapshotToRoom(room, snapshot) {
    if (!snapshot || typeof snapshot !== "object") return;

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
    const testingOn = Boolean(
        extractSnapshotValue(snapshot, ["40009", "testing_dimulai", "testing_running"], false),
    );

    if (!Number.isNaN(rawIn)) {
        const bar = rawIn > 50 ? rawIn / 100 : rawIn;
        room.pres_in = +bar.toFixed(2);
    }
    if (!Number.isNaN(processPressure)) {
        if (room.tp === "TEST PIT") room.test_pressure = +processPressure.toFixed(2);
        else room.pres_bbm = +processPressure.toFixed(2);
    }
    if (!Number.isNaN(pres2)) room.pres_2 = +pres2.toFixed(2);

    room.reg_roof_open = roofOpenBit;
    room.reg_roof_closed = roofClosed;
    room.roof_moving_open = roofMovingOpen;
    room.roof_moving_close = roofMovingClose;

    if (roofMovingOpen || roofMovingClose) room.roof_state = "STANDBY";
    else if (roofOpenBit) room.roof_state = "OPEN";
    else if (roofClosed) room.roof_state = "CLOSE";
    else if (!roofOpenBit && !roofClosed) room.roof_state = room.roof_state || "CLOSE";

    room.alarm_emergency = alarmAlert;
    room.alarm_pressure_in = alarmAlert;
    room.mode = maintenance ? "MAINTENANCE" : "AUTO";
    room.door_lock = doorLocked ? "locked" : "unlocked";
    room.phase = testingOn ? "RUNNING" : "STANDBY";
}

async function refreshCurrentRoomFromApi() {
    if (isRefreshingRoom || !ui.curRoomId) return;
    const room = findRoom(ui.curRoomId);
    if (!room || !room.api_room_id) return;

    isRefreshingRoom = true;
    try {
        const payload = await PlcAPI.getRoomData(room.api_room_id);
        if (payload?.data) applySnapshotToRoom(room, payload.data);
    } catch (err) {
        console.warn("[Realtime] room polling gagal:", err?.message || err);
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
            ["cr1", "cr2", "cr3"].forEach(tickRoom);
            tickUtil();
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
}
