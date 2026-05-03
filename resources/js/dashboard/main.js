import { store, ui, ROOM_TO_CR } from "./state.js";

let cameraStream = null;

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

function roofLineClass(rs) {
    if (rs === "OPEN") return "room-roof--open";
    if (rs === "STANDBY") return "room-roof--stb";
    return "room-roof--close";
}

function modeBadgeClass(m) {
    return m === "MAINTENANCE"
        ? "room-mode-badge--mt"
        : "room-mode-badge--auto";
}

export function buildSidebarRooms() {
    Object.entries(store.data).forEach(([crId, d]) => {
        const cont = document.getElementById(`rooms-${crId}`);
        if (!cont) return;
        cont.innerHTML = d.rooms
            .map((r) => {
                ROOM_TO_CR[r.id] = crId;
                const isCell = r.tp === "TEST CELL";
                const ico = isCell ? "ti-engine" : "ti-arrows-down";
                const rs = r.roof_state || "CLOSE";
                const md = r.mode || "AUTO";
                return `<div class="room-item" id="ri-${r.id}" onclick="openRoomPanel('${r.id}','${crId}')">
        <div class="room-dot" style="background:${r.col}"></div>
        <i class="ti ${ico}"></i>
        <div class="room-info">
          <div class="room-row-top">
            <span class="room-nm">${r.nm}</span>
            <span class="room-mode-badge ${modeBadgeClass(md)}">${md}</span>
          </div>
          <div class="room-roof-line ${roofLineClass(rs)}"><i class="ti ti-roof"></i>${rs}</div>
        </div>
      </div>`;
            })
            .join("");
    });
}

export function toggleRooms(crId) {
    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    const isOpen = cont.classList.contains("open");
    ["cr1", "cr2", "cr3"].forEach((k) => {
        document.getElementById(`rooms-${k}`).classList.remove("open");
        document.getElementById(`exp-${k}`).classList.remove("open");
    });
    if (!isOpen) {
        cont.classList.add("open");
        btn.classList.add("open");
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
        console.error("Error membuka kamera:", err);
        alert("Kamera tidak bisa diakses!");
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

    showCamera();

    ui.panelMode = "room";
    ui.curRoomId = roomId;
    ui.curId = crId;

    const rp = document.getElementById("rpanel");
    const col = r.col || "#059652";
    document.getElementById("rp-accent").style.background = col;
    rp.style.setProperty("--ra", col);

    const bc = document.getElementById("rp-breadcrumb");
    bc.classList.remove("is-hidden");
    bc.innerHTML = `<span class="bc-link" onclick="openPanel('${crId}')">${crD.nm}</span>
    <span class="bc-sep">›</span>
    <span>${r.nm}</span>`;

    document.getElementById("rp-type").textContent = r.tp;
    document.getElementById("rp-name").textContent = r.nm;
    document.getElementById("rp-name").style.color = col;
    document.getElementById("rp-zone").textContent = `Dimonitor: ${crD.nm}`;

    ["tab-s", "tab-l", "tab-e"].forEach((t) => {
        const el = document.getElementById(t);
        if (el) el.style.display = "";
    });

    renderRoomPressure(r, crId);
    renderRoomDoorLock(r, crId);
    renderRoomLog(r);
    swTab("s");
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

    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(() => {
        if (ui.panelMode !== "room" || !ui.curRoomId) return;
        const crIdCur = findCR(ui.curRoomId);
        if (crIdCur) tickRoom(crIdCur);
        const rr = findRoom(ui.curRoomId);
        if (!rr) return;
        renderRoomPressure(rr, crIdCur);
    }, 3000);
}

/** Tab TEKANAN: semua room menampilkan SENSOR PRESSURE dengan nama yang seragam */
export function renderRoomPressure(r, crId) {
    const testing = isRoomActivelyTesting(r);
    const statusLabel = testing ? "Sedang pengujian" : "Tidak sedang pengujian";
    const statusCl = testing ? "wa" : "ok";
    const rs = r.roof_state || "CLOSE";
    const md = r.mode || "AUTO";

    const p = r.pres_bbm ?? r.test_pressure ?? 0;
    const pressureBlock = `
  <div class="rp-sec">SENSOR PRESSURE</div>
  <div class="mc-grid">
    <div class="mc P" style="grid-column:1/-1">
      <div class="mc-lbl">SENSOR PRESSURE</div>
      <div class="mc-val">${p}</div>
      <div class="mc-st ok">PROSES</div>
    </div>
  </div>`;

    document.getElementById("pane-s").innerHTML = `
  <button class="rp-back" onclick="openPanel('${crId}')">
    <i class="ti ti-arrow-left"></i>KEMBALI KE CONTROL ROOM
  </button>
  ${pressureBlock}
  <div class="rp-sec">STATUS TEST</div>
  <div style="background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:12px 14px;margin-bottom:8px">
    <div style="font-family:var(--M);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:8px;font-weight:600">STATUS UJI</div>
    <div class="mc-st ${statusCl}" style="margin-top:0;font-size:12px">${statusLabel}</div>
    <div class="room-roof-line ${roofLineClass(rs)}" style="margin-top:10px"><i class="ti ti-roof"></i>ATAP: ${rs}</div>
    <div class="room-tags room-tags--modeonly">
      <span class="room-mode-badge ${modeBadgeClass(md)}">${md}</span>
    </div>
  </div>
  <div style="margin-top:6px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString("en-GB")}</span>
  </div>`;
}

export function renderRoomDoorLock(r, crId) {
    const locked = r.door_lock === "locked";
    document.getElementById("pane-l").innerHTML = `
  <button class="rp-back" onclick="openPanel('${crId}')">
    <i class="ti ti-arrow-left"></i>KEMBALI KE CONTROL ROOM
  </button>
  <div class="rp-sec">SWITCH DOOR LOCK</div>
  <div class="lk-item">
    <i class="ti ${locked ? "ti-lock" : "ti-lock-open"} lk-ico" style="color:${locked ? "var(--ok)" : "var(--cr)"}"></i>
    <div style="flex:1">
      <div class="lk-name">Pintu ruang uji</div>
      <div class="lk-time">Switch door lock ${locked ? "ON (terkunci)" : "OFF (terbuka)"}</div>
    </div>
    <div class="lk-badge ${locked ? "lk" : "ul"}">${locked ? "LOCKED" : "OPEN"}</div>
  </div>`;
}

export function renderRoomLog(r) {
    const ev = r.events && r.events.length ? r.events : [];
    let h = `<div class="rp-sec">LOG RUANGAN</div>`;
    if (!ev.length) {
        h += `<div style="font-family:var(--M);font-size:11px;color:var(--dim);padding:8px 0">Belum ada entri log.</div>`;
    } else {
        ev.forEach((e) => {
            h += `<div class="ev-row">
      <div class="ev-t">${e.t}</div>
      <div class="ev-d ${e.c}"></div>
      <div class="ev-m">${e.m}</div>
    </div>`;
        });
    }
    document.getElementById("pane-e").innerHTML = h;
}

export function openPanel(id) {
    if (!store.data[id]) return;
    ui.panelMode = "cr";
    ui.curId = id;
    ui.curRoomId = null;
    const d = store.data[id];
    const rp = document.getElementById("rpanel");
    document.getElementById("rp-accent").style.background = "#1564c0";
    rp.style.setProperty("--ra", "#1564c0");

    const bc = document.getElementById("rp-breadcrumb");
    bc.classList.add("is-hidden");
    bc.innerHTML = "";

    document.getElementById("rp-type").textContent = d.tp;
    document.getElementById("rp-name").textContent = d.nm;
    document.getElementById("rp-name").style.color = "#1564c0";
    document.getElementById("rp-zone").textContent = d.zn;

    ["tab-s", "tab-l", "tab-e"].forEach((t) => {
        const el = document.getElementById(t);
        if (el) el.style.display = "";
    });

    renderSensor(id);
    renderLock(id);
    renderEvents(id);
    swTab("s");
    rp.classList.add("open");
    ["cr1", "cr2", "cr3"].forEach((k) =>
        document
            .getElementById(`nav-${k}`)
            .classList.toggle("active", k === id),
    );
    document
        .querySelectorAll(".room-item")
        .forEach((el) => el.classList.remove("active"));
    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(() => {
        if (!ui.curId || ui.panelMode !== "cr") return;
        tickRoom(ui.curId);
        renderSensor(ui.curId);
    }, 3200);
}

export function closePanel() {
    document.getElementById("rpanel").classList.remove("open");
    ui.curId = null;
    ui.panelMode = "none";
    if (ui.lv) {
        clearInterval(ui.lv);
        ui.lv = null;
    }
    ["cr1", "cr2", "cr3"].forEach((k) =>
        document.getElementById(`nav-${k}`).classList.remove("active"),
    );
}

/** Control room — hanya tekanan saluran */
export function renderSensor(id) {
    const d = store.data[id];
    const p = d.line_pressure ?? 0;
    document.getElementById("pane-s").innerHTML = `
  <div class="rp-sec">TEKANAN SALURAN</div>
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
    const d = store.data[id];
    const locked = d.door_lock === "locked";
    document.getElementById("pane-l").innerHTML = `
  <div class="rp-sec">SWITCH DOOR LOCK</div>
  <div class="lk-item">
    <i class="ti ${locked ? "ti-lock" : "ti-lock-open"} lk-ico" style="color:${locked ? "var(--ok)" : "var(--cr)"}"></i>
    <div style="flex:1">
      <div class="lk-name">Area control room / akses uji</div>
      <div class="lk-time">Switch ${locked ? "terkunci" : "terbuka"}</div>
    </div>
    <div class="lk-badge ${locked ? "lk" : "ul"}">${locked ? "LOCKED" : "OPEN"}</div>
  </div>`;
}

export function renderEvents(id) {
    const d = store.data[id];
    let h = `<div class="rp-sec">LOG TERBARU</div>`;
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
    ["s", "l", "e"].forEach((t) => {
        const tab = document.getElementById(`tab-${t}`);
        const pane = document.getElementById(`pane-${t}`);
        if (tab) tab.classList.remove("on");
        if (pane) pane.style.display = "none";
    });
    const activeTab = document.getElementById(`tab-${n}`);
    const activePane = document.getElementById(`pane-${n}`);
    if (activeTab) activeTab.classList.add("on");
    if (activePane) activePane.style.display = "block";
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
    document.getElementById("al-count").textContent = count;
    document.getElementById("al-count").className =
        "bc " +
        (count > 0 ? (allAl.some((a) => a.lv === "cr") ? "cr" : "wa") : "ok");
    const list = document.getElementById("al-list");
    if (!count) {
        list.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>Tidak ada alarm</div>`;
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

    const areaList = document.getElementById("area-list");
    if (!areaAlerts.length) {
        areaList.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>Tidak ada area alert</div>`;
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

export function startLiveLoop() {
    setInterval(() => {
        ["cr1", "cr2", "cr3"].forEach(tickRoom);
        tickUtil();
        updateAlarmSidebar();
        updateUtilSidebar();
        if (ui.curId && ui.panelMode === "cr") renderSensor(ui.curId);
    }, 4000);
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
}
