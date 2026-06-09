import { store, ui } from "./state.js";

function dummyDashboardEnabled() {
    return import.meta.env.VITE_DASHBOARD_USE_DUMMY === "true";
}

function isAdminUser() {
    const u = typeof window.__SCADA_USER__ !== "undefined" ? window.__SCADA_USER__ : null;
    return u && u.role === "admin";
}

function collectRooms() {
    const list = [];
    for (const d of Object.values(store.data)) {
        for (const r of d.rooms || []) {
            list.push({ id: r.id, nm: r.nm || r.id });
        }
    }
    list.sort((a, b) => a.nm.localeCompare(b.nm, undefined, { numeric: true }));
    return list;
}

/**
 * Panel demo CV (admin + VITE_DASHBOARD_USE_DUMMY).
 * @param {{ findRoom: Function, findCR: Function, renderRoomPanelPanes: Function, setDummyCvDetection: Function }} main
 */
export function initDemoCvControls(main) {
    if (!dummyDashboardEnabled() || !isAdminUser()) return;

    const mapEl = document.getElementById("dash-map");
    if (!mapEl) return;

    const panel = document.createElement("div");
    panel.id = "dash-demo-cv";
    panel.className = "dash-demo-panel dash-demo-cv";
    panel.setAttribute("role", "region");
    panel.setAttribute("aria-label", "Demo CV control");
    panel.innerHTML = `
      <div class="dash-demo-panel__head">
        <i class="ti ti-video" aria-hidden="true"></i>
        <span class="dash-demo-panel__title">Demo CV</span>
        <span class="dash-demo-panel__badge">Dummy data</span>
      </div>
      <label class="dash-demo-panel__label" for="dash-demo-cv-room">Room</label>
      <select id="dash-demo-cv-room" class="dash-demo-panel__select"></select>
      <label class="dash-demo-panel__label" for="dash-demo-cv-slot">Camera</label>
      <select id="dash-demo-cv-slot" class="dash-demo-panel__select">
        <option value="1">Kamera 1</option>
        <option value="2">Kamera 2</option>
      </select>
      <div class="dash-demo-panel__actions">
        <button type="button" class="dash-demo-panel__btn dash-demo-panel__btn--alert" data-detect="1">Human ON</button>
        <button type="button" class="dash-demo-panel__btn" data-detect="0">Human OFF</button>
      </div>
    `;
    mapEl.appendChild(panel);

    const roomSelect = panel.querySelector("#dash-demo-cv-room");
    const slotSelect = panel.querySelector("#dash-demo-cv-slot");

    function fillRoomOptions() {
        const rooms = collectRooms();
        const prev = roomSelect.value;
        roomSelect.innerHTML = rooms
            .map((r) => `<option value="${r.id}">${r.nm}</option>`)
            .join("");
        if (prev && rooms.some((r) => r.id === prev)) roomSelect.value = prev;
        else if (ui.curRoomId && rooms.some((r) => r.id === ui.curRoomId)) roomSelect.value = ui.curRoomId;
        else if (rooms.length) roomSelect.value = rooms[0].id;
    }

    function refreshRoomPanelIfOpen(roomId) {
        if (ui.panelMode !== "room" || ui.curRoomId !== roomId) return;
        const room = main.findRoom(roomId);
        const crId = main.findCR(roomId);
        if (room && crId) main.renderRoomPanelPanes(room, crId);
    }

    function applyDetection(detected) {
        const roomId = roomSelect.value;
        const slot = Number(slotSelect.value) || 1;
        main.setDummyCvDetection(roomId, slot, detected);
        refreshRoomPanelIfOpen(roomId);
    }

    panel.querySelectorAll("[data-detect]").forEach((btn) => {
        btn.addEventListener("click", () => {
            applyDetection(btn.getAttribute("data-detect") === "1");
        });
    });

    roomSelect.addEventListener("change", () => {
        if (ui.curRoomId === roomSelect.value) refreshRoomPanelIfOpen(roomSelect.value);
    });

    fillRoomOptions();

    window.__syncDemoCvSelect = (roomId) => {
        if (!roomId || !roomSelect) return;
        if ([...roomSelect.options].some((o) => o.value === roomId)) roomSelect.value = roomId;
    };
}
