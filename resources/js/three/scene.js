import * as THREE from 'three';
import { store, ui } from '../dashboard/state.js';

const Z = 22;

/**
 * @param {object} opts
 * @param {HTMLCanvasElement} opts.canvas
 * @param {HTMLElement} opts.lyrEl
 * @param {SVGSVGElement} opts.svgEl
 * @param {HTMLElement} opts.tipEl
 * @param {(id: string) => string | null} opts.findCR
 * @param {(roomId: string) => object | null} opts.findRoom
 * @param {(id: string) => void} opts.openPanel
 * @param {(roomId: string, crId: string) => void} opts.openRoomPanel
 */
export function createDashboardThreeScene({
    canvas,
    lyrEl,
    svgEl,
    tipEl,
    findCR,
    findRoom,
    openPanel,
    openRoomPanel,
}) {
    const renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ReinhardToneMapping;
    renderer.toneMappingExposure = 1.1;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xdce8f5);
    scene.fog = new THREE.FogExp2(0xdce8f5, 0.0053);

    const getA = () => window.innerWidth / window.innerHeight;
    const cam = new THREE.OrthographicCamera(-Z * getA(), Z * getA(), Z, -Z, 0.1, 500);
    cam.position.set(42, 34, 42);
    cam.lookAt(2, 1, 2);

    scene.add(new THREE.AmbientLight(0xd0e0f0, 2.2));
    const sun = new THREE.DirectionalLight(0xfff8e8, 2.9);
    sun.position.set(28, 44, 22);
    sun.castShadow = true;
    sun.shadow.mapSize.set(4096, 4096);
    const ss = sun.shadow.camera;
    ss.left = ss.bottom = -44;
    ss.right = ss.top = 44;
    ss.near = 1;
    ss.far = 160;
    sun.shadow.bias = -0.0008;
    scene.add(sun);
    const fillLight = new THREE.DirectionalLight(0x182840, 0.75);
    fillLight.position.set(-22, 8, -22);
    scene.add(fillLight);
    scene.add(new THREE.HemisphereLight(0xc8dff5, 0x8aaccc, 0.8));

    const ml = (h, e = 0, ei = 0) =>
        new THREE.MeshLambertMaterial({ color: h, emissive: e, emissiveIntensity: ei });
    const mld = (h, e = 0, ei = 0) =>
        new THREE.MeshLambertMaterial({ color: h, emissive: e, emissiveIntensity: ei, side: THREE.DoubleSide });
    const mt = (h, op, e = 0, ei = 0) =>
        new THREE.MeshLambertMaterial({
            color: h,
            transparent: true,
            opacity: op,
            emissive: e,
            emissiveIntensity: ei,
        });

    const GW = () => [mld(0xdde8dd), mld(0xd0ddd0), mt(0x000000, 0), mld(0x18202a), mld(0xdae6da), mld(0xd0ddd0)];
    const RW = () => [mld(0xf5e8e8), mld(0xd0ddd0), mt(0x000000, 0), mld(0x18202a), mld(0xdae6da), mld(0xd0ddd0)];
    const CRW_BLUE = () => [
        ml(0x0a1828, 0x041020, 0.35),
        ml(0x061018, 0x020814, 0.25),
        ml(0x0e2038, 0x062030, 0.55),
        ml(0x040810),
        ml(0x0c1e34, 0x051828, 0.4),
        ml(0x061018),
    ];
    const GLASS = mt(0x3870c0, 0.55, 0x183058, 0.55);
    const GLASS2 = mt(0x2858a8, 0.42, 0x0f1e3e, 0.6);
    const FRAME = ml(0x263040);
    const MTL = ml(0x1e2c3c);
    const CONC = ml(0x141e2c);
    const CONC2 = ml(0x0e1620);
    const LED = ml(0x0e2e18, 0x0ea040, 1.4);
    /** Kuning atap (selaras jalan / referensi layout) */
    const ROOF = ml(0xd4a000, 0xffc040, 0.12);

    const BODIES = [];

    /**
     * Atap pelana terbagi 2: animasi geser kiri/kanan untuk akses crane dari atas.
     * Target buka/tutup dihitung dari data ruangan (fase/status uji).
     */
    const roofActuators = new Map();

    /** Target geser atap 0=tutup penuh, 1=buka penuh, 0.5=STANDBY */
    function roofSlideTarget01(room) {
        if (!room) return 0;
        if (room.roof_state === 'OPEN') return 1;
        if (room.roof_state === 'STANDBY') return 0.5;
        if (room.roof_state === 'CLOSE') return 0;
        const ph = room.phase;
        const st = room.st;
        if (ph === 'IDLE' || ph === 'STANDBY' || ph === 'SETUP' || ph === 'COMPLETE') return 1;
        if (st === 'STANDBY') return 1;
        return 0;
    }

    /**
     * @param {string} id
     * @param {number} x
     * @param {number} z
     * @param {number} w
     * @param {number} d
     * @param {number} h tinggi badan gedung (sama seperti building())
     */
    function addRetractableGableRoof(id, x, z, w, d, h) {
        const cy = h / 2 + 0.22;
        const topY = cy + h / 2;
        const halfW = w / 2;
        const rise = Math.min(1.05, Math.max(0.42, w * 0.13));
        const L = Math.sqrt(halfW * halfW + rise * rise);
        const angle = Math.atan2(rise, halfW);
        const thick = 0.12;
        const depth = d + 0.1;
        const slide = Math.min(halfW * 0.92, 1.15);

        const left = new THREE.Mesh(new THREE.BoxGeometry(L, thick, depth), ROOF);
        left.position.set(x - halfW / 2, topY + rise / 2, z);
        left.rotation.z = angle;
        left.castShadow = true;
        left.receiveShadow = true;
        scene.add(left);

        const right = new THREE.Mesh(new THREE.BoxGeometry(L, thick, depth), ROOF);
        right.position.set(x + halfW / 2, topY + rise / 2, z);
        right.rotation.z = -angle;
        right.castShadow = true;
        right.receiveShadow = true;
        scene.add(right);

        roofActuators.set(id, {
            left,
            right,
            baseLX: left.position.x,
            baseRX: right.position.x,
            slide,
            open01: 0,
        });
    }
    const addm = (geo, mat, x, y, z, rx = 0, ry = 0, rz = 0) => {
        const me = new THREE.Mesh(geo, mat);
        me.position.set(x, y, z);
        me.rotation.set(rx, ry, rz);
        me.castShadow = true;
        me.receiveShadow = true;
        scene.add(me);
        return me;
    };
    const BOX = (w, h, d) => new THREE.BoxGeometry(w, h, d);

    const addEdge = (w, h, d, x, y, z, col, op = 1) => {
        const eg = new THREE.EdgesGeometry(BOX(w, h, d));
        const em = new THREE.LineSegments(eg, new THREE.LineBasicMaterial({ color: col, transparent: op < 1, opacity: op }));
        em.position.set(x, y, z);
        scene.add(em);
    };

    const addInnerColumns = (x, z, w, d, h) => {
        const cw = 0.04,
            ch = h,
            cy = h / 2 + 0.22,
            col = ml(0x1e2a38),
            ox = w / 2 - cw / 2,
            oz = d / 2 - cw / 2;
        addm(BOX(cw, ch, cw), col, x - ox, cy, z - oz);
        addm(BOX(cw, ch, cw), col, x + ox, cy, z - oz);
        addm(BOX(cw, ch, cw), col, x - ox, cy, z + oz);
        addm(BOX(cw, ch, cw), col, x + ox, cy, z + oz);
    };

    const addDoor = (bx, bz, w, d, h, face, offAlong = 0) => {
        const DW = 0.85,
            DH = 1.4,
            cy = h * 0.15 + DH / 2 + 0.22;
        let px = bx,
            pz = bz,
            ry = 0;
        if (face === 'front') {
            pz = bz + d / 2 + 0.04;
            px = bx + offAlong;
            ry = 0;
        } else if (face === 'left') {
            px = bx - w / 2 - 0.04;
            pz = bz + offAlong;
            ry = Math.PI / 2;
        } else if (face === 'right') {
            px = bx + w / 2 + 0.04;
            pz = bz + offAlong;
            ry = -Math.PI / 2;
        }
        const df = new THREE.Mesh(BOX(DW + 0.1, DH + 0.1, 0.08), FRAME);
        df.position.set(px, cy, pz);
        df.rotation.y = ry;
        df.castShadow = true;
        scene.add(df);
        const dg = new THREE.Mesh(BOX(DW, DH, 0.055), GLASS2);
        dg.position.set(px, cy, pz);
        dg.rotation.y = ry;
        scene.add(dg);
        const step = new THREE.Mesh(BOX(1.0, 0.06, 0.15), CONC);
        const so =
            face === 'front' ? [offAlong, 0, d / 2 + 0.18] : face === 'left' ? [-w / 2 - 0.18, 0, offAlong] : [w / 2 + 0.18, 0, offAlong];
        step.position.set(bx + so[0], h * 0.18 + 0.06, bz + so[2]);
        step.rotation.y = ry;
        step.castShadow = true;
        scene.add(step);
    };

    const addWins = (bx, bz, w, d, h, face, nw = 2) => {
        const WW = 0.68,
            WH = 0.54,
            wy = h * 0.54,
            sp = (face === 'front' || face === 'back' ? w : d) / (nw + 1);
        let ry = 0;
        if (face === 'left') ry = Math.PI / 2;
        if (face === 'right') ry = -Math.PI / 2;
        for (let i = 1; i <= nw; i++) {
            const off = (i - (nw + 1) / 2) * sp;
            let wx = bx,
                wz = bz,
                fOff = 0;
            if (face === 'front') {
                fOff = d / 2 + 0.04;
                wx = bx + off;
            }
            if (face === 'left') {
                fOff = -(w / 2 + 0.04);
                wz = bz + off;
            }
            if (face === 'right') {
                fOff = w / 2 + 0.04;
                wz = bz + off;
            }
            const wpx = wx + (face === 'left' || face === 'right' ? fOff : 0);
            const wpz = wz + (face === 'front' ? fOff : 0);
            const wf = new THREE.Mesh(BOX(WW + 0.1, WH + 0.08, 0.07), FRAME);
            wf.position.set(wpx, wy, wpz);
            wf.rotation.y = ry;
            wf.castShadow = true;
            scene.add(wf);
            const wg = new THREE.Mesh(BOX(WW, WH, 0.05), GLASS);
            wg.position.set(wpx, wy, wpz);
            wg.rotation.y = ry;
            scene.add(wg);
            const ws = new THREE.Mesh(BOX(WW + 0.15, 0.055, 0.12), MTL);
            ws.position.set(wpx, wy - WH / 2 - 0.03, wpz);
            ws.rotation.y = ry;
            scene.add(ws);
            const wl = new THREE.Mesh(BOX(WW * 0.8, 0.03, 0.02), LED);
            wl.position.set(wpx, wy + WH * 0.28, wpz);
            wl.rotation.y = ry;
            scene.add(wl);
        }
    };

    const building = (id, x, z, w, d, h, mats, _acol, doorCfg = null, winCfg = null) => {
        const cy = h / 2 + 0.22;
        addm(BOX(w + 0.28, 0.22, d + 0.28), CONC2, x, 0.11, z);
        const body = new THREE.Mesh(BOX(w, h, d), mats);
        body.position.set(x, cy, z);
        body.castShadow = true;
        body.receiveShadow = true;
        if (id) {
            body.userData.id = id;
            body.name = id;
            BODIES.push(body);
        }
        scene.add(body);
        addEdge(w, h, d, x, cy, z, 0x4a5568, 0.85);
        if (doorCfg) {
            const cfg = Array.isArray(doorCfg) ? doorCfg : [doorCfg];
            cfg.forEach((dc) => addDoor(x, z, w, d, h, dc.face, dc.off || 0));
        }
        if (winCfg) addWins(x, z, w, d, h, winCfg.face, winCfg.count || 2);
        return body;
    };

    addm(new THREE.PlaneGeometry(150, 150), ml(0xc8d8ea), 0, 0.001, 0, -Math.PI / 2);
    const slb = new THREE.Mesh(new THREE.PlaneGeometry(40, 34), ml(0x18202a));
    slb.rotation.x = -Math.PI / 2;
    slb.position.set(0, 0.01, 2);
    slb.receiveShadow = true;
    scene.add(slb);
    scene.add(new THREE.GridHelper(0, 0));
    [
        [40, 0.28, 0.32, 0, 0.14, -15.2],
        [40, 0.28, 0.32, 0, 0.14, 19.2],
        [0.32, 0.28, 34, -20, 0.14, 2],
        [0.32, 0.28, 34, 20, 0.14, 2],
    ].forEach(([w, h, d, x, y, z]) => {
        addm(BOX(w, h, d), ml(0x8aaac8), x, y, z);
        addEdge(w, h, d, x, y, z, 0x6090b8, 0.65);
    });

    const roadMat = ml(0xd4a000, 0xffd000, 0.35);
    const addRoad = (x1, z1, x2, z2, width = 1.0) => {
        const dx = x2 - x1,
            dz = z2 - z1,
            len = Math.sqrt(dx * dx + dz * dz),
            angle = Math.atan2(dx, dz);
        addm(BOX(width, 0.05, len), roadMat, (x1 + x2) / 2, 0.04, (z1 + z2) / 2, 0, angle, 0);
    };
    addRoad(-10, -15, -10, 3.5, 1);
    addRoad(6.7, -4, 6.7, 3.5, 1);
    addRoad(-20, 2, -10, 2, 1);
    addRoad(-10, 3, 7, 3, 1);
    addRoad(7, -3.5, 19.5, -3.5, 1);

    building('pit3', -14, -9, 6.3, 5.5, 3.0, GW(), 0x4a8460, { face: 'right', off: 0 }, null);
    addInnerColumns(-14, -9, 6.3, 5.5, 3.0);
    addRetractableGableRoof('pit3', -14, -9, 6.3, 5.5, 3.0);
    building('pit4', -14, -3.4, 6.3, 5.4, 3.0, RW(), 0xa83848, { face: 'right', off: 0 }, null);
    addInnerColumns(-14, -3.4, 6.3, 5.4, 3.0);
    addRetractableGableRoof('pit4', -14, -3.4, 6.3, 5.4, 3.0);
    building('cr3', -5.5, -11, 4.0, 2.0, 2.0, CRW_BLUE(), 0x1a8fff, { face: 'front', off: 0.7 }, { face: 'front', count: 2 });
    building('cell5', 1.8, -10, 6.5, 6, 3.0, GW(), 0xa83848, { face: 'right', off: 2 }, null);
    addRetractableGableRoof('cell5', 1.8, -10, 6.5, 6, 3.0);
    building('cell4', 1.8, -3.8, 6.5, 6, 3.0, RW(), 0xa83848, { face: 'front', off: 2.2 }, null);
    addRetractableGableRoof('cell4', 1.8, -3.8, 6.5, 6, 3.0);
    building('pit1', 10.8, -9, 4.5, 7, 3.0, RW(), 0xa83848, { face: 'front', off: -1.3 }, null);
    addInnerColumns(10.8, -9, 4.5, 7.0, 3.0);
    addRetractableGableRoof('pit1', 10.8, -9, 4.5, 7, 3.0);
    building('pit2', 15.5, -9, 4.5, 7, 3.0, RW(), 0xa83848, { face: 'front', off: 1.3 }, null);
    addInnerColumns(15.5, -9, 4.5, 7.0, 3.0);
    addRetractableGableRoof('pit2', 15.5, -9, 4.5, 7, 3.0);
    building('cr1', 10.5, 1.5, 4.0, 2.0, 2.0, CRW_BLUE(), 0x1a8fff, { face: 'front', off: 0.7 }, { face: 'front', count: 2 });
    building('cr2', 15.5, 1.5, 4.0, 2.0, 2.0, CRW_BLUE(), 0x1a8fff, { face: 'front', off: 0.7 }, { face: 'front', count: 2 });
    building('pit5', -12.3, 11, 5.8, 6.5, 3.0, GW(), 0x4a8460, null, null);
    addInnerColumns(-12.3, 11, 5.8, 6.5, 3.0);
    addRetractableGableRoof('pit5', -12.3, 11, 5.8, 6.5, 3.0);
    building('pit6', -6.2, 11, 5.8, 6.5, 3.0, GW(), 0x4a8460, null, null);
    addInnerColumns(-6.2, 11, 5.8, 6.5, 3.0);
    addRetractableGableRoof('pit6', -6.2, 11, 5.8, 6.5, 3.0);
    building('cell3', 1.8, 13, 6.7, 6.1, 3.0, GW(), 0x4a8460, null, null);
    addRetractableGableRoof('cell3', 1.8, 13, 6.7, 6.1, 3.0);
    building('cell2', 8.6, 13, 6.7, 6.1, 3.0, GW(), 0x4a8460, null, null);
    addRetractableGableRoof('cell2', 8.6, 13, 6.7, 6.1, 3.0);
    building('cell1', 15.5, 13, 6.7, 6.1, 3.0, GW(), 0x4a8460, null, null);
    addRetractableGableRoof('cell1', 15.5, 13, 6.7, 6.1, 3.0);

    const LBLS = [
        { id: 'cr3', anchor: new THREE.Vector3(-5.5, 2.5, -11), t: 'CONTROL ROOM 3', ox: 80, oy: -120, by: -120 },
        { id: 'cr1', anchor: new THREE.Vector3(10.5, 2.5, 1.5), t: 'CONTROL ROOM 1', ox: 140, oy: 130, by: 0 },
        { id: 'cr2', anchor: new THREE.Vector3(15.5, 2.5, 1.5), t: 'CONTROL ROOM 2', ox: 60, oy: 140, by: 0 },
        { id: 'pit3', anchor: new THREE.Vector3(-14, 3.2, -9), t: 'TEST PIT 3', ox: -80, oy: -70, by: -70 },
        { id: 'pit4', anchor: new THREE.Vector3(-14, 3.2, -3.4), t: 'TEST PIT 4', ox: -120, oy: -60, by: -60 },
        { id: 'pit5', anchor: new THREE.Vector3(-12.3, 3.2, 11), t: 'TEST PIT 5', ox: -110, oy: -70, by: -70 },
        { id: 'pit6', anchor: new THREE.Vector3(-6.2, 3.2, 11), t: 'TEST PIT 6', ox: -215, oy: -25, by: -25 },
        { id: 'cell5', anchor: new THREE.Vector3(1.8, 3.2, -10), t: 'TEST CELL 5', ox: 80, oy: -120, by: -120 },
        { id: 'cell4', anchor: new THREE.Vector3(1.8, 3.2, -3.8), t: 'TEST CELL 4', ox: 160, oy: -60, by: -60 },
        { id: 'pit1', anchor: new THREE.Vector3(10.8, 3.2, -9), t: 'TEST PIT 1', ox: 90, oy: -50, by: -50 },
        { id: 'pit2', anchor: new THREE.Vector3(15.5, 3.2, -9), t: 'TEST PIT 2', ox: 80, oy: -40, by: -40 },
        { id: 'cell3', anchor: new THREE.Vector3(1.8, 3.2, 13), t: 'TEST CELL 3', ox: -70, oy: 110, by: 0 },
        { id: 'cell2', anchor: new THREE.Vector3(8.6, 3.2, 13), t: 'TEST CELL 2', ox: -70, oy: 125, by: 0 },
        { id: 'cell1', anchor: new THREE.Vector3(15.5, 3.2, 13), t: 'TEST CELL 1', ox: -70, oy: 130, by: 0 },
    ];

    LBLS.forEach((lb) => {
        const div = document.createElement('div');
        div.className = 'lbl lbl-ext';
        div.innerHTML = `<div class="lbl-ext-name">${lb.t}</div>`;
        div.style.opacity = '0';
        div.addEventListener('click', () => {
            if (store.data[lb.id]) {
                openPanel(lb.id);
            } else {
                const crId = findCR(lb.id);
                if (crId) openRoomPanel(lb.id, crId);
            }
        });
        lyrEl.appendChild(div);
        lb.el = div;
    });

    const RC = new THREE.Raycaster();
    const MV = new THREE.Vector2();

    canvas.addEventListener('mousemove', (e) => {
        MV.x = (e.clientX / window.innerWidth) * 2 - 1;
        MV.y = -((e.clientY / window.innerHeight) * 2 - 1);
        RC.setFromCamera(MV, cam);
        const hits = RC.intersectObjects(BODIES, false);
        if (hits.length) {
            const id = hits[0].object.userData.id;
            const crId = findCR(id);
            if (crId) {
                ui.hov = id;
                canvas.style.cursor = 'pointer';
                const isCR = !!store.data[id];
                const d = store.data[crId];
                tipEl.style.borderTopColor = isCR ? '#1564c0' : d.col;
                tipEl.style.color = isCR ? '#1564c0' : d.col;
                const roomD = isCR ? null : findRoom(id);
                const label = roomD ? `${roomD.nm}` : d.nm;
                tipEl.textContent = `${label} · Klik untuk detail`;
                tipEl.style.left = `${e.clientX + 14}px`;
                tipEl.style.top = `${e.clientY - 14}px`;
                tipEl.style.opacity = '1';
            }
        } else {
            ui.hov = null;
            canvas.style.cursor = '';
            tipEl.style.opacity = '0';
        }
    });

    canvas.addEventListener('mousedown', (e) => {
        ui.mdx = e.clientX;
        ui.mdy = e.clientY;
    });

    canvas.addEventListener('click', (e) => {
        if (Math.abs(e.clientX - ui.mdx) > 6 || Math.abs(e.clientY - ui.mdy) > 6) return;
        MV.x = (e.clientX / window.innerWidth) * 2 - 1;
        MV.y = -((e.clientY / window.innerHeight) * 2 - 1);
        RC.setFromCamera(MV, cam);
        const hits = RC.intersectObjects(BODIES, false);
        if (hits.length) {
            const id = hits[0].object.userData.id;
            if (store.data[id]) {
                openPanel(id);
            } else {
                const crId = findCR(id);
                if (crId) openRoomPanel(id, crId);
            }
        }
    });

    const TMP = new THREE.Vector3();
    let T = 0;
    let raf = 0;

    const animate = () => {
        raf = requestAnimationFrame(animate);
        T += 0.016;
        BODIES.forEach((b) => {
            const isH = ui.hov === b.userData.id;
            const isSel =
                ui.curId &&
                store.data[ui.curId] &&
                (b.userData.id === ui.curId || store.data[ui.curId].rooms.find((r) => r.id === b.userData.id));
            const ms = Array.isArray(b.material) ? b.material : [b.material];
            ms.forEach((m) => {
                if (m.emissive && m._base === undefined) m._base = m.emissiveIntensity;
                if (m.emissive) {
                    if (isSel) m.emissiveIntensity = (m._base || 0) + 0.28 + Math.sin(T * 3) * 0.1;
                    else if (isH) m.emissiveIntensity = (m._base || 0) + 0.18;
                    else m.emissiveIntensity = m._base || 0;
                }
            });
        });

        roofActuators.forEach((rec, buildingId) => {
            const room = findRoom(buildingId);
            const target = roofSlideTarget01(room);
            rec.open01 = THREE.MathUtils.lerp(rec.open01, target, 0.06);
            rec.left.position.x = rec.baseLX - rec.slide * rec.open01;
            rec.right.position.x = rec.baseRX + rec.slide * rec.open01;
        });

        LBLS.forEach((lb) => {
            TMP.copy(lb.anchor).project(cam);
            if (TMP.z >= 1) {
                lb.el.style.opacity = '0';
                lb._vis = false;
                return;
            }
            lb._vis = true;
            lb._sx = (TMP.x * 0.5 + 0.5) * window.innerWidth;
            lb._sy = (-0.5 * TMP.y + 0.5) * window.innerHeight;
            lb._lx = lb._sx + lb.ox;
            lb._ly = lb._sy + lb.oy;
            lb.el.style.opacity = '1';
            lb.el.style.left = `${lb._lx}px`;
            lb.el.style.top = `${lb._ly}px`;
            lb.el.style.transform = 'translate(-50%,-50%)';
        });

        svgEl.setAttribute('width', window.innerWidth);
        svgEl.setAttribute('height', window.innerHeight);
        svgEl.innerHTML = LBLS.map((lb) => {
            if (!lb._vis || !lb._sx) return '';
            const ew = (lb.el.offsetWidth || 80) / 2;
            const ex = lb._lx + (lb._sx > lb._lx ? ew : -ew);
            const ey = lb._ly;
            let points;
            if (lb.by !== 0 && lb.by !== undefined) {
                const midY = lb._sy + lb.by,
                    midX = ex;
                points = `${lb._sx},${lb._sy} ${lb._sx},${midY} ${midX},${midY} ${ex},${ey}`;
            } else {
                points = `${lb._sx},${lb._sy} ${ex},${lb._sy} ${ex},${ey}`;
            }
            return `
      <polyline points="${points}" fill="none" stroke="#2d4a6a" stroke-width="1.2" opacity="0.85"/>
      <circle cx="${lb._sx}" cy="${lb._sy}" r="2.5" fill="#4a6080" opacity="0.85"/>
      <circle cx="${ex}" cy="${ey}" r="1.5" fill="#4a6080" opacity="0.6"/>`;
        }).join('');

        renderer.render(scene, cam);
    };

    const onResize = () => {
        const A = getA();
        cam.left = -Z * A;
        cam.right = Z * A;
        cam.top = Z;
        cam.bottom = -Z;
        cam.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    };

    window.addEventListener('resize', onResize);

    return {
        start() {
            animate();
        },
        dispose() {
            cancelAnimationFrame(raf);
            window.removeEventListener('resize', onResize);
            renderer.dispose();
        },
    };
}
