import { store, ui, ROOM_TO_CR } from './state.js';

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

export function buildSidebarRooms() {
    Object.entries(store.data).forEach(([crId, d]) => {
        const cont = document.getElementById(`rooms-${crId}`);
        if (!cont) return;
        cont.innerHTML = d.rooms
            .map((r) => {
                ROOM_TO_CR[r.id] = crId;
                const isCell = r.tp === 'TEST CELL';
                const stCl = r.st === 'ACTIVE' || r.st === 'OPERATIONAL' ? 'ok' : r.st === 'STANDBY' ? 'sb' : 'wa';
                const ico = isCell ? 'ti-engine' : 'ti-arrows-down';
                return `<div class="room-item" id="ri-${r.id}" onclick="openRoomPanel('${r.id}','${crId}')">
        <div class="room-dot" style="background:${r.col}"></div>
        <i class="ti ${ico}"></i>
        <div class="room-nm">${r.nm}</div>
        <div class="room-st ${stCl}" id="rst-${r.id}">${r.st}</div>
      </div>`;
            })
            .join('');
    });
}

export function toggleRooms(crId) {
    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    const isOpen = cont.classList.contains('open');
    ['cr1', 'cr2', 'cr3'].forEach((k) => {
        document.getElementById(`rooms-${k}`).classList.remove('open');
        document.getElementById(`exp-${k}`).classList.remove('open');
    });
    if (!isOpen) {
        cont.classList.add('open');
        btn.classList.add('open');
    }
}

export async function showCamera() {
    const camView = document.getElementById('camera-view');
    const video = document.getElementById('camera');
    camView.classList.add('is-visible');
    if (cameraStream) {
        video.srcObject = cameraStream;
        return;
    }
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' },
        });
        video.srcObject = cameraStream;
    } catch (err) {
        console.error('Error membuka kamera:', err);
        alert('Kamera tidak bisa diakses!');
    }
}

export function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach((t) => t.stop());
        cameraStream = null;
    }
    document.getElementById('camera-view').classList.remove('is-visible');
}

export function openRoomPanel(roomId, crId) {
    console.log('ROOM DIKLIK');
    const r = findRoom(roomId);
    const crD = store.data[crId];
    if (!r || !crD) return;

    showCamera();

    ui.panelMode = 'room';
    ui.curRoomId = roomId;
    ui.curId = crId;

    const rp = document.getElementById('rpanel');
    const col = r.col || '#059652';
    document.getElementById('rp-accent').style.background = col;
    rp.style.setProperty('--ra', col);

    const bc = document.getElementById('rp-breadcrumb');
    bc.classList.remove('is-hidden');
    bc.innerHTML = `<span class="bc-link" onclick="openPanel('${crId}')">${crD.nm}</span>
    <span class="bc-sep">›</span>
    <span>${r.nm}</span>`;

    document.getElementById('rp-type').textContent = r.tp;
    document.getElementById('rp-name').textContent = r.nm;
    document.getElementById('rp-name').style.color = col;
    document.getElementById('rp-zone').textContent = `Dimonitor: ${crD.nm}`;

    ['tab-s', 'tab-l', 'tab-r', 'tab-e', 'tab-u', 'tab-m', 'tab-p'].forEach((t) => (document.getElementById(t).style.display = 'none'));
    document.getElementById('tab-s').style.display = '';
    document.getElementById('tab-e').style.display = '';
    if (r.tp === 'TEST CELL') {
        document.getElementById('tab-m').style.display = '';
    } else {
        document.getElementById('tab-p').style.display = '';
    }

    renderRoomEnv(r);
    if (r.tp === 'TEST CELL') renderRoomCell(r);
    else renderRoomPit(r);
    renderEvents(crId);
    swTab('s');
    rp.classList.add('open');

    ['cr1', 'cr2', 'cr3'].forEach((k) => document.getElementById(`nav-${k}`).classList.remove('active'));
    document.getElementById(`nav-${crId}`).classList.add('active');
    document.querySelectorAll('.room-item').forEach((el) => el.classList.remove('active'));
    const ri = document.getElementById(`ri-${roomId}`);
    if (ri) ri.classList.add('active');

    const cont = document.getElementById(`rooms-${crId}`);
    const btn = document.getElementById(`exp-${crId}`);
    cont.classList.add('open');
    btn.classList.add('open');

    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(() => {
        if (ui.panelMode !== 'room' || !ui.curRoomId) return;
        const crIdCur = findCR(ui.curRoomId);
        if (crIdCur) tickRoom(crIdCur);
        const rr = findRoom(ui.curRoomId);
        if (!rr) return;
        renderRoomEnv(rr);
        if (rr.tp === 'TEST CELL') renderRoomCell(rr);
        else renderRoomPit(rr);
    }, 3000);
}

export function renderRoomEnv(r) {
    const tW = r.temp > 25,
        hH = r.hum > 60;
    const crId = findCR(r.id);
    document.getElementById('pane-s').innerHTML = `
  <button class="rp-back" onclick="openPanel('${crId}')">
    <i class="ti ti-arrow-left"></i>KEMBALI KE CONTROL ROOM
  </button>
  <div class="rp-sec">SENSOR RUANGAN</div>
  <div class="mc-grid">
    <div class="mc T">
      <div class="mc-lbl">TEMPERATURE</div>
      <div class="mc-val">${r.temp} °C</div>
      <div class="mc-st ${tW ? 'wa' : 'ok'}">${tW ? 'TINGGI' : 'NORMAL'}</div>
    </div>
    <div class="mc H">
      <div class="mc-lbl">HUMIDITY</div>
      <div class="mc-val">${r.hum} % RH</div>
      <div class="mc-st ${hH ? 'wa' : 'ok'}">${hH ? 'TINGGI' : 'NORMAL'}</div>
    </div>
    <div class="mc P">
      <div class="mc-lbl">PRESSURE</div>
      <div class="mc-val sm">${r.pres || 1013} hPa</div>
      <div class="mc-st ok">NORMAL</div>
    </div>
  </div>
  <div style="margin-top:8px;background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:10px 12px">
    <div style="font-family:var(--M);font-size:9px;color:var(--dim);letter-spacing:2px;margin-bottom:6px;font-weight:600">INFO TEST</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">TEST ID</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--txt);font-weight:600">${r.test_id || '—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">FASE</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--amber);font-weight:600">${r.phase || '—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">DURASI</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--in);font-weight:600">${r.test_dur || '—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">STATUS</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--ok);font-weight:600">${r.st || '—'}</div>
    </div>
  </div>
  <div style="margin-top:6px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString('en-GB')}</span>
  </div>`;
}

export function renderRoomCell(r) {
    const tOil = r.temp_oli > 80 ? 'wa' : r.temp_oli > 70 ? 'amb' : 'ok';
    const tCool = r.temp_coolant > 90 ? 'cr' : r.temp_coolant > 82 ? 'wa' : 'ok';
    const phaseColors = ['var(--dim)', 'var(--in)', 'var(--ok)', 'var(--amber)', 'var(--ok)'];
    const phaseIdx = r.phase_step || 0;
    document.getElementById('pane-m').innerHTML = `
  <div class="rp-sec">PARAMETER MESIN</div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-engine" style="color:var(--ok);font-size:14px"></i>
      <div class="rd-env-title">PERFORMA</div>
      <div style="font-family:var(--M);font-size:8px;color:var(--ok)">● RUNNING</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">RPM</div>
          <div class="rd-cell-val in">${r.rpm}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">TORQUE</div>
          <div class="rd-cell-val">${r.torque} <span style="font-size:9px;color:var(--muted)">Nm</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">OUTPUT</div>
          <div class="rd-cell-val ok">${r.power_out} <span style="font-size:9px;color:var(--muted)">kW</span></div>
        </div>
      </div>
      <div style="margin-top:5px;background:var(--s2);border-radius:3px;padding:7px 8px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">LOAD</div>
          <div style="font-family:var(--M);font-size:10px;font-weight:700;color:${r.load > 85 ? 'var(--wa)' : 'var(--ok)'}">${r.load}%</div>
        </div>
        <div style="height:4px;background:var(--b1);border-radius:2px;overflow:hidden">
          <div style="height:100%;width:${Math.min(r.load, 100)}%;background:${r.load > 85 ? 'var(--wa)' : 'var(--ok)'};border-radius:2px;transition:width 1s"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-temperature" style="color:var(--amber);font-size:14px"></i>
      <div class="rd-env-title">TERMAL</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. OLI</div>
          <div class="rd-cell-val ${tOil}">${r.temp_oli}°C</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. COOLANT</div>
          <div class="rd-cell-val ${tCool}">${r.temp_coolant}°C</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. EXHAUST</div>
          <div class="rd-cell-val amb">${r.temp_exhaust}°C</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-droplet" style="color:var(--in);font-size:14px"></i>
      <div class="rd-env-title">BAHAN BAKAR & COOLANT</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid2">
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW BBM</div>
          <div class="rd-cell-val">${r.flow_bbm} <span style="font-size:9px;color:var(--muted)">L/h</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">PRES. BBM</div>
          <div class="rd-cell-val">${r.pres_bbm} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW COOLANT</div>
          <div class="rd-cell-val">${r.flow_coolant} <span style="font-size:9px;color:var(--muted)">L/m</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">KONSUMSI</div>
          <div class="rd-cell-val">${r.konsumsi_listrik} <span style="font-size:9px;color:var(--muted)">kW</span></div>
        </div>
      </div>
    </div>
  </div>
  <div style="margin-top:4px;background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:8px 12px">
    <div style="font-family:var(--M);font-size:8px;color:var(--muted);margin-bottom:5px;letter-spacing:1.5px">FASE TEST</div>
    <div style="display:flex;gap:3px;margin-bottom:4px">
      ${['IDLE', 'SETUP', 'RUNNING', 'HOLDING', 'COMPLETE']
          .map(
              (ph, i) => `
        <div style="flex:1;height:4px;border-radius:2px;background:${i < phaseIdx ? 'var(--ok)' : i === phaseIdx ? 'var(--amber)' : 'var(--b1)'}"></div>
      `,
          )
          .join('')}
    </div>
    <div style="font-family:var(--M);font-size:9px;color:${phaseColors[phaseIdx]};font-weight:600;text-align:center">${r.phase}</div>
  </div>`;
}

export function renderRoomPit(r) {
    const pct = r.target_pressure > 0 ? Math.round((r.test_pressure / r.target_pressure) * 100) : 0;
    const pCol = pct >= 100 ? 'var(--ok)' : pct > 80 ? 'var(--amber)' : 'var(--in)';
    const maxSt = r.max_st === 'RUNNING' ? 'ok' : r.max_st === 'STANDBY' ? 'sb' : 'cr';
    document.getElementById('pane-p').innerHTML = `
  <div class="rp-sec">TEST PRESSURE</div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-gauge" style="color:${pCol};font-size:14px"></i>
      <div class="rd-env-title">PRESSURE GAUGE</div>
      <div style="font-family:var(--M);font-size:8px;color:${pCol}">${pct}%</div>
    </div>
    <div class="rd-env-body">
      <div style="text-align:center;margin-bottom:8px">
        <div style="font-family:var(--M);font-size:36px;font-weight:700;color:${pCol};line-height:1">${r.test_pressure}</div>
        <div style="font-family:var(--M);font-size:10px;color:var(--muted)">bar</div>
      </div>
      <div style="height:6px;background:var(--b1);border-radius:4px;overflow:hidden;margin-bottom:6px">
        <div style="height:100%;width:${Math.min(pct, 100)}%;background:${pCol};border-radius:4px;transition:width 1.2s"></div>
      </div>
      <div style="display:flex;justify-content:space-between">
        <div style="font-family:var(--M);font-size:8px;color:var(--muted)">0 bar</div>
        <div style="font-family:var(--M);font-size:8px;color:var(--txt);font-weight:600">TARGET: ${r.target_pressure} bar</div>
      </div>
      <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:5px">
        <div class="rd-cell">
          <div class="rd-cell-lbl">RATE</div>
          <div class="rd-cell-val">${r.pressure_rate} <span style="font-size:9px;color:var(--muted)">bar/min</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">HOLD TIME</div>
          <div class="rd-cell-val">${r.hold_time} <span style="font-size:9px;color:var(--muted)">min</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">FASE</div>
          <div class="rd-cell-val amb">${r.phase}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">DURASI</div>
          <div class="rd-cell-val in">${r.test_dur}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-device-analytics" style="color:var(--amber);font-size:14px"></i>
      <div class="rd-env-title">MAXIMATOR ${r.max_model}</div>
      <div class="rm-st ${maxSt}" style="font-size:8px">${r.max_st}</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">INLET</div>
          <div class="rd-cell-val">${r.max_inlet} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">OUTLET</div>
          <div class="rd-cell-val ${pct > 90 ? 'wa' : 'ok'}">${r.max_outlet} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">RATIO</div>
          <div class="rd-cell-val">${r.max_ratio}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-droplet" style="color:var(--in);font-size:14px"></i>
      <div class="rd-env-title">FLUIDA</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW IN</div>
          <div class="rd-cell-val">${r.flow_in} <span style="font-size:9px;color:var(--muted)">L/m</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">JENIS</div>
          <div class="rd-cell-val in">${r.fluid_type || '—'}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. FLUIDA</div>
          <div class="rd-cell-val">${r.fluid_temp}°C</div>
        </div>
      </div>
    </div>
  </div>`;
}

export function openPanel(id) {
    if (!store.data[id]) return;
    ui.panelMode = 'cr';
    ui.curId = id;
    ui.curRoomId = null;
    const d = store.data[id];
    const rp = document.getElementById('rpanel');
    document.getElementById('rp-accent').style.background = '#1564c0';
    rp.style.setProperty('--ra', '#1564c0');

    const bc = document.getElementById('rp-breadcrumb');
    bc.classList.add('is-hidden');
    bc.innerHTML = '';

    document.getElementById('rp-type').textContent = d.tp;
    document.getElementById('rp-name').textContent = d.nm;
    document.getElementById('rp-name').style.color = '#1564c0';
    document.getElementById('rp-zone').textContent = d.zn;
    ['tab-s', 'tab-l', 'tab-r', 'tab-e'].forEach((t) => (document.getElementById(t).style.display = ''));
    ['tab-u', 'tab-m', 'tab-p'].forEach((t) => (document.getElementById(t).style.display = 'none'));
    updatePanelStatus(id);
    renderSensor(id);
    renderLock(id);
    renderRooms(id);
    renderEvents(id);
    swTab('s');
    rp.classList.add('open');
    ['cr1', 'cr2', 'cr3'].forEach((k) => document.getElementById(`nav-${k}`).classList.toggle('active', k === id));
    document.querySelectorAll('.room-item').forEach((el) => el.classList.remove('active'));
    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(() => {
        if (!ui.curId || ui.panelMode !== 'cr') return;
        tickRoom(ui.curId);
        renderSensor(ui.curId);
        updatePanelStatus(ui.curId);
    }, 3200);
}

export function openUtilPanel() {
    ui.panelMode = 'util';
    ui.curId = null;
    const rp = document.getElementById('rpanel');
    document.getElementById('rp-accent').style.background = '#0369a1';
    rp.style.setProperty('--ra', '#0369a1');
    document.getElementById('rp-type').textContent = 'SISTEM UTILITAS';
    document.getElementById('rp-name').textContent = 'Utilitas Fasilitas';
    document.getElementById('rp-name').style.color = '#0369a1';
    document.getElementById('rp-zone').textContent = 'PAM · LISTRIK · HVAC';
    ['tab-s', 'tab-l', 'tab-r', 'tab-e'].forEach((t) => (document.getElementById(t).style.display = 'none'));
    document.getElementById('tab-u').style.display = '';
    swTab('u');
    renderUtil();
    rp.classList.add('open');
    ['cr1', 'cr2', 'cr3'].forEach((k) => document.getElementById(`nav-${k}`).classList.remove('active'));
    if (ui.lv) clearInterval(ui.lv);
    ui.lv = setInterval(() => {
        if (ui.panelMode !== 'util') return;
        tickUtil();
        renderUtil();
        updateUtilSidebar();
    }, 3000);
}

export function updatePanelStatus(_id) {}

export function closePanel() {
    document.getElementById('rpanel').classList.remove('open');
    ui.curId = null;
    ui.panelMode = 'none';
    if (ui.lv) {
        clearInterval(ui.lv);
        ui.lv = null;
    }
    ['cr1', 'cr2', 'cr3'].forEach((k) => document.getElementById(`nav-${k}`).classList.remove('active'));
}

export function renderSensor(id) {
    const d = store.data[id];
    const tH = d.temp > 26,
        hH = d.hum > 60,
        pW = d.pwr > 90;
    document.getElementById('pane-s').innerHTML = `
  <div class="rp-sec">SENSOR CONTROL ROOM</div>
  <div class="mc-grid">
    <div class="mc T">
      <div class="mc-lbl">TEMPERATURE</div>
      <div class="mc-val">${d.temp} °C</div>
      <div class="mc-st ${tH ? 'wa' : 'ok'}">${tH ? 'TINGGI' : 'NORMAL'}</div>
    </div>
    <div class="mc H">
      <div class="mc-lbl">HUMIDITY</div>
      <div class="mc-val">${d.hum} % RH</div>
      <div class="mc-st ${hH ? 'wa' : 'ok'}">${hH ? 'TINGGI' : 'NORMAL'}</div>
    </div>
    <div class="mc W">
      <div class="mc-lbl">POWER LOAD</div>
      <div class="mc-val">${d.pwr} %</div>
      <div class="mc-st ${pW ? 'wa' : 'ok'}">${pW ? 'TINGGI' : 'NORMAL'}</div>
    </div>
    <div class="mc P">
      <div class="mc-lbl">VOLTAGE</div>
      <div class="mc-val">${d.volt} V</div>
      <div class="mc-st ok">STABIL</div>
    </div>
    <div class="mc EG">
      <div class="mc-lbl">FREQUENCY</div>
      <div class="mc-val">${d.freq.toFixed(1)} Hz</div>
      <div class="mc-st ok">NORMAL</div>
    </div>
    <div class="mc CY">
      <div class="mc-lbl">POWER FACTOR</div>
      <div class="mc-val">${d.pf.toFixed(2)}</div>
      <div class="mc-st ${d.pf >= 0.85 ? 'ok' : 'wa'}">${d.pf >= 0.85 ? 'BAIK' : 'RENDAH'}</div>
    </div>
  </div>
  <div style="margin-top:10px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString('en-GB')}</span>
  </div>`;
}

export function renderLock(id) {
    const d = store.data[id];
    const locked = d.locks.filter((l) => l.st === 'locked').length,
        total = d.locks.length;
    const pct = Math.round((locked / total) * 100),
        sc = locked < total ? 'var(--wa)' : 'var(--ok)';
    let h = `
  <div class="rp-sec">LOCK SYSTEM</div>
  <div style="background:var(--s1);border:1px solid var(--b1);border-radius:5px;
    padding:12px 14px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
    <div>
      <div style="font-family:var(--M);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:4px;font-weight:600">TERKUNCI</div>
      <div style="font-family:var(--M);font-size:24px;font-weight:700;color:${sc}">${locked}<span style="font-size:10px;color:var(--dim)"> / ${total}</span></div>
    </div>
    <div style="text-align:right">
      <div style="font-family:var(--M);font-size:10px;color:var(--dim);margin-bottom:6px;font-weight:600">KEAMANAN</div>
      <div style="width:80px;height:5px;background:var(--b1);border-radius:3px;overflow:hidden;margin-bottom:5px">
        <div style="height:100%;width:${pct}%;background:${sc};border-radius:3px"></div>
      </div>
      <div style="font-family:var(--M);font-size:10px;color:${sc};font-weight:600">${pct}%</div>
    </div>
  </div>`;
    d.locks.forEach((l) => {
        h += `<div class="lk-item">
      <i class="ti ${l.st === 'locked' ? 'ti-lock' : 'ti-lock-open'} lk-ico" style="color:${l.st === 'locked' ? 'var(--ok)' : 'var(--cr)'}"></i>
      <div style="flex:1"><div class="lk-name">${l.name}</div><div class="lk-time">${l.time}</div></div>
      <div class="lk-badge ${l.st === 'locked' ? 'lk' : 'ul'}">${l.st === 'locked' ? 'LOCKED' : 'OPEN'}</div>
    </div>`;
    });
    document.getElementById('pane-l').innerHTML = h;
}

export function renderRooms(id) {
    const d = store.data[id];
    let h = `<div class="rp-sec">RUANGAN DIMONITOR (${d.rooms.length})</div>`;
    d.rooms.forEach((r) => {
        const isCell = r.tp === 'TEST CELL',
            isPit = r.tp === 'TEST PIT';
        const stCl = r.st === 'ACTIVE' || r.st === 'OPERATIONAL' ? 'ok' : r.st === 'STANDBY' ? 'sb' : 'wa';

        if (isCell) {
            const phaseColors = ['var(--dim)', 'var(--in)', 'var(--ok)', 'var(--amber)', 'var(--ok)'];
            const phaseIdx = r.phase_step;
            h += `<div class="rm-row" style="flex-direction:column;align-items:stretch;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="openRoomPanel('${r.id}','${id}')">
          <div class="rm-dot" style="background:${r.col}"></div>
          <div class="rm-info">
            <div class="rm-name">${r.nm}</div>
            <div class="rm-type">${r.tp} · <span style="color:var(--muted)">${r.test_id}</span></div>
          </div>
          <div class="rm-st ${stCl}">${r.st}</div>
          <i class="ti ti-chevron-right" style="font-size:12px;color:var(--muted);flex-shrink:0"></i>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;padding:0 2px">
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">RPM</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.rpm}</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">TORQUE</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.torque} Nm</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">OUTPUT</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.power_out} kW</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.OLI</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${r.temp_oli > 80 ? 'var(--wa)' : 'var(--txt)'}">${r.temp_oli}°C</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.COOLANT</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${r.temp_coolant > 90 ? 'var(--wa)' : 'var(--txt)'}">${r.temp_coolant}°C</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLOW BBM</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.flow_bbm} L/h</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;padding:0 2px">
          <div style="font-family:var(--M);font-size:8px;color:var(--dim);flex-shrink:0">FASE:</div>
          <div style="font-family:var(--M);font-size:9px;font-weight:700;color:${phaseColors[phaseIdx]}">${r.phase}</div>
          <div style="flex:1"></div>
          <div style="font-family:var(--M);font-size:8px;color:var(--dim)">LOAD: <span style="color:${r.load > 85 ? 'var(--wa)' : 'var(--txt)'};font-weight:600">${r.load}%</span></div>
          <div style="font-family:var(--M);font-size:8px;color:var(--dim)">DUR: <span style="color:var(--in);font-weight:600">${r.test_dur}</span></div>
        </div>
      </div>`;
        } else if (isPit) {
            const pct = r.target_pressure > 0 ? Math.round((r.test_pressure / r.target_pressure) * 100) : 0;
            const pbar_col = pct >= 100 ? 'var(--ok)' : pct > 80 ? 'var(--amber)' : 'var(--in)';
            const maxSt = r.max_st === 'RUNNING' ? 'ok' : r.max_st === 'STANDBY' ? 'sb' : 'cr';
            h += `<div class="rm-row" style="flex-direction:column;align-items:stretch;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="openRoomPanel('${r.id}','${id}')">
          <div class="rm-dot" style="background:${r.col}"></div>
          <div class="rm-info">
            <div class="rm-name">${r.nm}</div>
            <div class="rm-type">${r.tp} · <span style="color:var(--muted)">${r.test_id}</span></div>
          </div>
          <div class="rm-st ${stCl}">${r.st}</div>
          <i class="ti ti-chevron-right" style="font-size:12px;color:var(--muted);flex-shrink:0"></i>
        </div>
        <div style="background:var(--s2);border-radius:4px;padding:8px 10px">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">TEST PRESSURE</div>
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">TARGET: <span style="color:var(--txt);font-weight:600">${r.target_pressure} bar</span></div>
          </div>
          <div style="font-family:var(--M);font-size:20px;font-weight:700;color:${pbar_col};line-height:1">${r.test_pressure} <span style="font-size:10px;font-weight:400;color:var(--dim)">bar</span></div>
          <div style="height:5px;background:var(--b1);border-radius:3px;margin-top:6px;overflow:hidden">
            <div style="height:100%;width:${Math.min(pct, 100)}%;background:${pbar_col};border-radius:3px;transition:width 1s"></div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:3px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">FASE: <span style="color:${r.phase === 'HOLDING' ? 'var(--amber)' : r.phase === 'IDLE' ? 'var(--dim)' : 'var(--in)'};font-weight:600">${r.phase}</span></div>
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">${pct}%</div>
          </div>
        </div>
        <div style="background:var(--s2);border-radius:4px;padding:6px 10px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim);letter-spacing:1.5px">MAXIMATOR ${r.max_model}</div>
            <div class="rm-st ${maxSt}" style="font-size:8px">${r.max_st}</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">
            <div>
              <div style="font-family:var(--M);font-size:8px;color:var(--muted)">INLET</div>
              <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.max_inlet} bar</div>
            </div>
            <div>
              <div style="font-family:var(--M);font-size:8px;color:var(--muted)">OUTLET</div>
              <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${pbar_col}">${r.max_outlet} bar</div>
            </div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLOW IN</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.flow_in} <span style="font-size:8px">L/m</span></div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLUID</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.fluid}</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.FLUID</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.fluid_temp}°C</div>
          </div>
        </div>
      </div>`;
        }
    });
    document.getElementById('pane-r').innerHTML = h;
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
    document.getElementById('pane-e').innerHTML = h;
}

export function renderUtil() {
    const u = store.util;
    const p1 = u.pam.pump1,
        p2 = u.pam.pump2;
    const lvl = u.pam.tank_level;
    const lvlCol = lvl < 20 ? 'var(--cr)' : lvl < 40 ? 'var(--wa)' : 'var(--ok)';
    const pwrSt = u.listrik.st === 'NORMAL' ? 'ok' : 'wa';
    document.getElementById('pane-u').innerHTML = `
  <div class="rp-sec">MOTOR PAM</div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-droplet util-card-ico" style="color:var(--in)"></i>
      <div class="util-card-title">${p1.nm}</div>
      <div class="rm-st ok">${p1.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">FLOW RATE</span><span class="util-data-val">${p1.flow} L/min</span></div>
      <div class="util-data-row"><span class="util-data-lbl">TEKANAN</span><span class="util-data-val">${p1.pres} bar</span></div>
      <div class="util-data-row"><span class="util-data-lbl">ARUS MOTOR</span><span class="util-data-val ${p1.amp > 20 ? 'wa' : 'ok'}">${p1.amp} A</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SUHU MOTOR</span><span class="util-data-val ${p1.temp_motor > 75 ? 'wa' : 'ok'}">${p1.temp_motor}°C</span></div>
      <div class="util-data-row"><span class="util-data-lbl">JAM OPERASI</span><span class="util-data-val">${p1.run_hours} h</span></div>
    </div>
  </div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-droplet util-card-ico" style="color:var(--muted)"></i>
      <div class="util-card-title">${p2.nm}</div>
      <div class="rm-st sb">${p2.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">STATUS</span><span class="util-data-val ok">SIAP PAKAI</span></div>
      <div class="util-data-row"><span class="util-data-lbl">JAM OPERASI</span><span class="util-data-val">${p2.run_hours} h</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SUHU MOTOR</span><span class="util-data-val ok">${p2.temp_motor}°C</span></div>
    </div>
  </div>
  <div class="rp-sec">TANGKI AIR</div>
  <div class="util-card">
    <div class="util-card-body">
      <div class="util-data-row">
        <span class="util-data-lbl">LEVEL TANGKI</span>
        <span class="util-data-val" style="color:${lvlCol}">${lvl}%</span>
      </div>
      <div class="level-bar-wrap">
        <div class="level-bar-outer"><div class="level-bar-inner" style="width:${lvl}%;background:${lvlCol}"></div></div>
        <div class="level-bar-pct" style="color:${lvlCol}">${lvl}%</div>
      </div>
      <div class="util-data-row" style="margin-top:8px"><span class="util-data-lbl">TOTAL HARI INI</span><span class="util-data-val">${u.pam.total_flow_hari} L</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SERVICE TERAKHIR</span><span class="util-data-val">${u.pam.last_service}</span></div>
    </div>
  </div>
  <div class="rp-sec">PANEL LISTRIK</div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-bolt util-card-ico" style="color:var(--ok)"></i>
      <div class="util-card-title">Monitor Daya</div>
      <div class="rm-st ${pwrSt}">${u.listrik.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">TOTAL DAYA</span><span class="util-data-val">${u.listrik.total_kw} kW</span></div>
      <div class="util-data-row"><span class="util-data-lbl">POWER FACTOR</span><span class="util-data-val ${u.listrik.pf >= 0.85 ? 'ok' : 'wa'}">${u.listrik.pf}</span></div>
      <div class="util-data-row"><span class="util-data-lbl">FREKUENSI</span><span class="util-data-val">${u.listrik.freq} Hz</span></div>
      <div style="height:1px;background:var(--b1);margin:8px 0"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
        ${['R', 'S', 'T']
            .map((ph, i) => {
                const v = [u.listrik.volt_r, u.listrik.volt_s, u.listrik.volt_t][i];
                const a = [u.listrik.amp_r, u.listrik.amp_s, u.listrik.amp_t][i];
                return `<div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">PHASE ${ph}</div>
            <div style="font-family:var(--M);font-size:10px;font-weight:600;color:var(--txt)">${v}V</div>
            <div style="font-family:var(--M);font-size:9px;color:var(--dim)">${a}A</div>
          </div>`;
            })
            .join('')}
      </div>
      <div class="util-data-row" style="margin-top:8px"><span class="util-data-lbl">kWh HARI INI</span><span class="util-data-val">${u.listrik.kwh_hari} kWh</span></div>
    </div>
  </div>
  <div class="rp-sec">HVAC / AC</div>
  ${[u.hvac.unit1, u.hvac.unit2, u.hvac.unit3]
      .map(
          (ac) => `
  <div class="util-card" style="margin-bottom:6px">
    <div class="util-card-hdr">
      <i class="ti ti-air-conditioning util-card-ico" style="color:var(--wa)"></i>
      <div class="util-card-title">${ac.nm}</div>
      <div class="rm-st ok">${ac.st}</div>
    </div>
    <div class="util-card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">SET</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:var(--in)">${ac.set}°C</div>
        </div>
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">AKTUAL</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:${ac.actual > ac.set + 1 ? 'var(--wa)' : 'var(--ok)'}">${ac.actual}°C</div>
        </div>
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">ARUS</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:var(--txt)">${ac.amp}A</div>
        </div>
      </div>
    </div>
  </div>`,
      )
      .join('')}
  <div style="font-family:var(--M);font-size:9px;color:var(--dim);margin-top:4px">
    Filter: <span style="color:var(--ok);font-weight:600">${u.hvac.filter_st}</span> · 
    Service berikutnya: <span style="color:var(--in)">${u.hvac.next_service}</span>
  </div>`;
}

export function swTab(n) {
    ['s', 'l', 'r', 'e', 'u', 'm', 'p'].forEach((t) => {
        const tab = document.getElementById(`tab-${t}`);
        const pane = document.getElementById(`pane-${t}`);
        if (tab) tab.classList.remove('on');
        if (pane) pane.style.display = 'none';
    });
    const activeTab = document.getElementById(`tab-${n}`);
    const activePane = document.getElementById(`pane-${n}`);
    if (activeTab) activeTab.classList.add('on');
    if (activePane) activePane.style.display = 'block';
}

export function getAlarms(id) {
    const d = store.data[id];
    const al = [];
    if (d.temp > 26) al.push({ lv: 'cr', nm: `${d.nm}: Suhu ${d.temp}°C`, sub: 'Melewati batas 26°C', id, time: getNow() });
    else if (d.temp > 24) al.push({ lv: 'wa', nm: `${d.nm}: Suhu ${d.temp}°C`, sub: 'Mendekati batas', id, time: getNow() });
    if (d.hum > 60) al.push({ lv: 'wa', nm: `${d.nm}: Kelembaban ${d.hum}%`, sub: 'Di atas normal', id, time: getNow() });
    if (d.pwr > 90) al.push({ lv: 'wa', nm: `${d.nm}: Daya ${d.pwr}%`, sub: 'Beban tinggi', id, time: getNow() });
    d.locks
        .filter((l) => l.st === 'unlocked')
        .forEach((l) => al.push({ lv: 'wa', nm: `${d.nm}: ${l.name}`, sub: 'Tidak terkunci', id, time: l.time }));
    d.rooms.forEach((r) => {
        if (r.tp === 'TEST CELL') {
            if (r.temp_oli > 80) al.push({ lv: 'wa', nm: `${r.nm}: Suhu Oli ${r.temp_oli}°C`, sub: 'Mendekati batas 85°C', id, time: getNow() });
            if (r.temp_coolant > 90) al.push({ lv: 'wa', nm: `${r.nm}: Suhu Coolant ${r.temp_coolant}°C`, sub: 'Mendekati batas', id, time: getNow() });
        }
        if (r.tp === 'TEST PIT') {
            if (r.test_pressure > 0 && r.test_pressure >= r.target_pressure)
                al.push({
                    lv: 'in',
                    nm: `${r.nm}: Target Pressure Tercapai`,
                    sub: `${r.test_pressure}/${r.target_pressure} bar`,
                    id,
                    time: getNow(),
                });
        }
    });
    return al;
}

export function getNow() {
    return new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

export function updateAlarmSidebar() {
    const allAl = [...getAlarms('cr1'), ...getAlarms('cr2'), ...getAlarms('cr3')];
    const count = allAl.length;
    document.getElementById('al-count').textContent = count;
    document.getElementById('al-count').className = 'bc ' + (count > 0 ? (allAl.some((a) => a.lv === 'cr') ? 'cr' : 'wa') : 'ok');
    const list = document.getElementById('al-list');
    if (!count) {
        list.innerHTML = `<div class="al-empty-row"><i class="ti ti-circle-check"></i>Tidak ada alarm</div>`;
    } else {
        list.innerHTML = allAl
            .map(
                (a) => `
      <div class="al-row" onclick="openPanel('${a.id}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt"><div class="al-name">${a.nm}</div><div class="al-sub">${a.sub}</div></div>
        <div class="al-time">${a.time}</div>
      </div>`,
            )
            .join('');
    }
    ['cr1', 'cr2', 'cr3'].forEach((k) => {
        const al = getAlarms(k);
        const hasCr = al.some((a) => a.lv === 'cr'),
            hasWa = al.some((a) => a.lv === 'wa');
        const badge = document.getElementById(`nb-${k}`);
        if (hasCr) {
            badge.className = 'cr-badge cr';
            badge.textContent = 'ALARM';
        } else if (hasWa) {
            badge.className = 'cr-badge wa';
            badge.textContent = 'WARN';
        } else {
            badge.className = 'cr-badge ok';
            badge.textContent = 'OK';
        }
    });
    document.getElementById('hkpi-alarm').textContent = count;
    document.getElementById('hkpi-alarm-dot').style.background =
        count > 0 ? (allAl.some((a) => a.lv === 'cr') ? 'var(--cr)' : 'var(--wa)') : 'var(--ok)';
    let active = 0;
    Object.values(store.data).forEach((d) => d.rooms.forEach((r) => {
        if (r.st === 'ACTIVE' || r.st === 'OPERATIONAL') active++;
    }));
    document.getElementById('hkpi-active').textContent = active;
}

export function updateUtilSidebar() {
    document.getElementById('sb-pam-val').textContent = `${store.util.pam.pump1.flow.toFixed(1)} L/min · ${store.util.pam.pump1.pres.toFixed(1)} bar`;
    document.getElementById('sb-pwr-val').textContent = `${store.util.listrik.total_kw.toFixed(1)} kW · PF ${store.util.listrik.pf.toFixed(2)}`;
    document.getElementById('hkpi-pwr').textContent = `${store.util.listrik.total_kw.toFixed(0)} kW`;
}

export function tickRoom(id) {
    const d = store.data[id];
    d.temp = +(d.temp + (Math.random() - 0.5) * 0.25).toFixed(1);
    d.hum = Math.max(40, Math.min(76, Math.round(d.hum + (Math.random() - 0.5))));
    d.pwr = +(d.pwr + (Math.random() - 0.5) * 0.4).toFixed(1);
    d.freq = +(49.8 + Math.random() * 0.4).toFixed(1);
    d.pf = +Math.min(0.99, Math.max(0.82, d.pf + (Math.random() - 0.5) * 0.01)).toFixed(2);
    d.rooms.forEach((r) => {
        r.temp = +(r.temp + (Math.random() - 0.5) * 0.2).toFixed(1);
        r.hum = Math.max(40, Math.min(76, Math.round(r.hum + (Math.random() - 0.5))));
        if (r.tp === 'TEST CELL') {
            r.rpm = Math.max(800, Math.round(r.rpm + (Math.random() - 0.5) * 20));
            r.torque = +(r.torque + (Math.random() - 0.5) * 2).toFixed(1);
            r.power_out = +(r.power_out + (Math.random() - 0.5) * 0.5).toFixed(1);
            r.temp_oli = +(r.temp_oli + (Math.random() - 0.5) * 0.3).toFixed(1);
            r.temp_coolant = +(r.temp_coolant + (Math.random() - 0.5) * 0.4).toFixed(1);
            r.flow_bbm = +(r.flow_bbm + (Math.random() - 0.5) * 0.2).toFixed(1);
            if (r.load != null) r.load = +(r.load + (Math.random() - 0.5) * 0.5).toFixed(1);
        }
        if (r.tp === 'TEST PIT' && r.max_st === 'RUNNING') {
            if (r.test_pressure < r.target_pressure)
                r.test_pressure = +Math.min(r.target_pressure, r.test_pressure + r.pressure_rate * 0.05).toFixed(1);
            r.max_outlet = r.test_pressure;
            r.flow_in = +(r.flow_in + (Math.random() - 0.5) * 0.5).toFixed(1);
            r.fluid_temp = +(r.fluid_temp + (Math.random() - 0.5) * 0.1).toFixed(1);
        }
    });
}

export function tickUtil() {
    store.util.pam.pump1.flow = +(store.util.pam.pump1.flow + (Math.random() - 0.5) * 2).toFixed(1);
    store.util.pam.pump1.pres = +(store.util.pam.pump1.pres + (Math.random() - 0.5) * 0.1).toFixed(1);
    store.util.pam.pump1.amp = +(store.util.pam.pump1.amp + (Math.random() - 0.5) * 0.3).toFixed(1);
    store.util.pam.pump1.temp_motor = +(store.util.pam.pump1.temp_motor + (Math.random() - 0.5) * 0.2).toFixed(1);
    store.util.listrik.total_kw = +(store.util.listrik.total_kw + (Math.random() - 0.5) * 3).toFixed(1);
    store.util.listrik.pf = +Math.min(0.99, Math.max(0.82, store.util.listrik.pf + (Math.random() - 0.5) * 0.01)).toFixed(2);
    store.util.listrik.amp_r = +(store.util.listrik.amp_r + (Math.random() - 0.5) * 0.5).toFixed(1);
    store.util.listrik.amp_s = +(store.util.listrik.amp_s + (Math.random() - 0.5) * 0.5).toFixed(1);
    store.util.listrik.amp_t = +(store.util.listrik.amp_t + (Math.random() - 0.5) * 0.5).toFixed(1);
    store.util.hvac.unit1.actual = +(store.util.hvac.unit1.actual + (Math.random() - 0.5) * 0.1).toFixed(1);
    store.util.hvac.unit2.actual = +(store.util.hvac.unit2.actual + (Math.random() - 0.5) * 0.1).toFixed(1);
    store.util.hvac.unit3.actual = +(store.util.hvac.unit3.actual + (Math.random() - 0.5) * 0.1).toFixed(1);
}

export function startLiveLoop() {
    setInterval(() => {
        ['cr1', 'cr2', 'cr3'].forEach(tickRoom);
        tickUtil();
        updateAlarmSidebar();
        updateUtilSidebar();
        if (ui.curId && ui.panelMode === 'cr') {
            renderSensor(ui.curId);
            renderRooms(ui.curId);
        }
    }, 4000);
}

export function toggleSidebar() {
    const sb = document.getElementById('lsb'),
        ov = document.getElementById('overlay');
    const open = sb.classList.toggle('open');
    ov.style.display = 'block';
    setTimeout(() => (ov.style.opacity = open ? '1' : '0'), 10);
    if (!open) setTimeout(() => (ov.style.display = 'none'), 300);
}

export function closeSidebar() {
    document.getElementById('lsb').classList.remove('open');
    const ov = document.getElementById('overlay');
    ov.style.opacity = '0';
    setTimeout(() => (ov.style.display = 'none'), 300);
}

export function initClock() {
    const tick = () => {
        const n = new Date(),
            p = (v) => String(v).padStart(2, '0');
        document.getElementById('clk').textContent = `${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
        setTimeout(tick, 1000);
    };
    tick();
}

export function initLogoFallback() {
    const img = document.querySelector('.logo-img');
    const fb = document.querySelector('.logo-fallback');
    if (!img || !fb) return;
    img.addEventListener('error', () => {
        img.classList.add('is-broken');
        fb.classList.add('is-visible');
    });
}

export function initOptionalTabs() {
    ['tab-u', 'tab-m', 'tab-p'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

export function registerGlobals() {
    window.toggleSidebar = toggleSidebar;
    window.closeSidebar = closeSidebar;
    window.openPanel = openPanel;
    window.openRoomPanel = openRoomPanel;
    window.openUtilPanel = openUtilPanel;
    window.closePanel = closePanel;
    window.swTab = swTab;
    window.toggleRooms = toggleRooms;
}
