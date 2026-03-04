<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPM Oil & Gas</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#06080d;--s1:#0a0e16;--s2:#0f1520;--s3:#141c2a;
  --b1:#192433;--b2:#223040;--b3:#2c3e54;
  --txt:#c8dce8;--dim:#4a6274;--muted:#7a96a8;--white:#fff;
  --ok:#00c85a;--ok2:rgba(0,200,90,.08);--ok3:rgba(0,200,90,.28);
  --wa:#ffaa00;--wa2:rgba(255,170,0,.08);--wa3:rgba(255,170,0,.28);
  --cr:#ff3c3c;--cr2:rgba(255,60,60,.08);--cr3:rgba(255,60,60,.28);
  --in:#1a8fff;--in2:rgba(26,143,255,.08);--in3:rgba(26,143,255,.28);
  --amber:#b45309;
  /* WARNA CONTROL ROOM BARU - biru seragam */
  --cr-col:#1a8fff;
  --F:'Barlow',sans-serif;
  --FC:'Barlow Condensed',sans-serif;
  --M:'IBM Plex Mono',monospace;
  --LW:248px; --RW:316px; --HDR:52px;
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;overflow:hidden;background:var(--bg);color:var(--txt);font-family:var(--F)}
#cv{position:fixed;inset:0}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9980;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.04) 2px,rgba(0,0,0,.04) 4px)}
.hdr{position:fixed;top:0;left:0;right:0;height:var(--HDR);z-index:700;
  background:rgba(4,6,11,.97);border-bottom:1px solid var(--amber);
  display:flex;align-items:center;padding:0 15px;gap:12px}
.logo{display:flex;align-items:center;gap:8px;flex-shrink:0}
.logo-img{height:30px;width:auto;object-fit:contain}
.logo-fallback{font-family:var(--FC);font-size:13px;font-weight:700;color:var(--wa);letter-spacing:2px;display:none}
.hdr-sep{width:1px;height:26px;background:var(--b1);flex-shrink:0}
.hdr-r{display:flex;align-items:center;gap:8px;margin-left:auto}
@keyframes dp{0%,100%{opacity:1}50%{opacity:.12}}
.clk{font-family:var(--M);font-size:16px;font-weight:600;color:var(--txt);letter-spacing:2px}
.ham{display:none;background:none;border:1px solid var(--b2);color:var(--muted);
  border-radius:3px;width:30px;height:30px;cursor:pointer;
  align-items:center;justify-content:center;font-size:18px}
.lsb{position:fixed;top:var(--HDR);left:0;bottom:0;width:var(--LW);
  background:var(--s1);border-right:1px solid var(--b1);
  display:flex;flex-direction:column;z-index:500;overflow:hidden;
  transition:transform .28s cubic-bezier(.4,0,.2,1)}
.lscroll{flex:1;overflow-y:auto;overflow-x:hidden}
.lscroll::-webkit-scrollbar{width:2px}
.lscroll::-webkit-scrollbar-thumb{background:var(--b2)}
.blk{border-bottom:1px solid var(--b1)}
.bh{display:flex;align-items:center;justify-content:space-between;padding:8px 12px}
.bt{font-family:var(--M);font-size:9px;letter-spacing:2px;color:var(--txt);
  display:flex;align-items:center;gap:6px;text-transform:uppercase}
.bt i{font-size:14px;color:var(--txt)}
.bc{font-family:var(--M);font-size:10px;padding:2px 7px;border-radius:2px;letter-spacing:1px}
.bc.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.bc.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.bc.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.bc.in{background:var(--in2);color:var(--in);border:1px solid var(--in3)}
.bb{padding:6px 12px 10px}
.cr-item{display:flex;align-items:center;gap:9px;padding:9px 12px;
  cursor:pointer;border-left:2px solid transparent;
  transition:all .15s;border-bottom:1px solid rgba(25,36,51,.5)}
.cr-item:last-child{border-bottom:none}
.cr-item:hover{background:var(--s2)}
.cr-item.active{background:var(--s2);border-left-color:var(--ic,var(--in))}
.cr-info{flex:1;min-width:0}
.cr-name{font-family:var(--FC);font-size:12px;font-weight:600;letter-spacing:.5px}
.cr-sub{font-family:var(--M);font-size:8px;color:var(--muted);margin-top:2px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cr-badge{font-family:var(--M);font-size:9px;padding:2px 6px;border-radius:2px;flex-shrink:0}
.cr-badge.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.cr-badge.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.cr-badge.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.al-row{display:flex;align-items:flex-start;gap:7px;padding:6px 2px;
  border-bottom:1px solid rgba(25,36,51,.5);cursor:pointer;border-radius:2px;
  transition:background .12s}
.al-row:hover{background:var(--s2)}
.al-row:last-child{border:none}
.al-bar{width:3px;border-radius:2px;flex-shrink:0;align-self:stretch;min-height:26px}
.al-bar.cr{background:var(--cr);box-shadow:0 0 5px var(--cr)}
.al-bar.wa{background:var(--wa);box-shadow:0 0 3px var(--wa)}
.al-txt{flex:1;min-width:0}
.al-name{font-size:10px;font-weight:600;color:var(--txt);line-height:1.25}
.al-sub{font-family:var(--M);font-size:9px;color:var(--dim);margin-top:2px}
.al-time{font-family:var(--F);font-size:12px;letter-spacing:1px;color:var(--dim);flex-shrink:0}
.sys-blk{padding:10px 12px;border-top:1px solid var(--b1)}
.sys-title{font-family:var(--M);font-size:7px;color:var(--dim);letter-spacing:2px;margin-bottom:7px}
.sys-row{display:flex;align-items:center;gap:6px;margin-bottom:5px}
.sys-lbl{font-family:var(--M);font-size:7px;color:var(--dim);width:28px}
.sys-bar{flex:1;height:3px;background:var(--b1);border-radius:2px;overflow:hidden}
.sys-fill{height:100%;border-radius:2px;transition:width 1s}
.sys-val{font-family:var(--M);font-size:7px;width:28px;text-align:right}
.lyr{position:fixed;inset:0;pointer-events:none;z-index:65}
.lbl{position:absolute;transform:translate(-50%,-50%);text-align:center;white-space:nowrap;pointer-events:none;line-height:1.35}
.lbl-r{font-family:var(--M);font-size:7px;color:rgba(155,195,218,.82);text-shadow:0 0 12px #000,0 1px 3px #000;letter-spacing:.5px}
.lbl-c{font-family:var(--FC);font-size:10px;font-weight:700;color:#fff;text-shadow:0 0 16px #000,0 0 7px currentColor}
.lbl-z{font-family:var(--M);font-size:7px;letter-spacing:4px;color:rgba(70,105,125,.35);text-transform:uppercase}
#tip{position:fixed;pointer-events:none;z-index:900;
  background:rgba(4,6,12,.97);border:1px solid var(--b2);border-top:2px solid;
  border-radius:0 0 3px 3px;padding:5px 13px;font-family:var(--M);font-size:8px;
  opacity:0;transition:opacity .1s;white-space:nowrap;box-shadow:0 8px 28px rgba(0,0,0,.85)}
.rpanel{position:fixed;top:var(--HDR);right:0;bottom:0;width:var(--RW);
  background:var(--s1);border-left:1px solid var(--b1);
  display:flex;flex-direction:column;z-index:600;
  transform:translateX(var(--RW));transition:transform .28s cubic-bezier(.4,0,.2,1)}
.rpanel.open{transform:translateX(0)}
.rp-accent{position:absolute;top:0;left:0;right:0;height:2px;background:var(--ra,var(--in))}
.rp-hdr{padding:12px 14px 10px;border-bottom:1px solid var(--b1);flex-shrink:0}
.rp-hdr-row{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px}
.rp-type{font-family:var(--M);font-size:7px;color:var(--dim);letter-spacing:3px;margin-bottom:3px}
.rp-name{font-family:var(--FC);font-size:15px;font-weight:700;letter-spacing:.5px}
.rp-zone{font-family:var(--M);font-size:7px;color:var(--dim);letter-spacing:1.5px;margin-top:3px}
.rp-close{background:var(--s2);border:1px solid var(--b2);color:var(--dim);
  width:26px;height:26px;border-radius:3px;cursor:pointer;
  display:grid;place-items:center;font-size:15px;transition:all .15s;flex-shrink:0}
.rp-close:hover{border-color:var(--cr);color:var(--cr);background:var(--cr2)}
.rp-status{display:flex;align-items:center;gap:6px;padding:5px 10px;border-radius:2px;
  font-family:var(--M);font-size:7px;letter-spacing:1px}
.rp-status.ok{background:var(--ok2);border:1px solid var(--ok3);color:var(--ok)}
.rp-status.wa{background:var(--wa2);border:1px solid var(--wa3);color:var(--wa)}
.rp-status.cr{background:var(--cr2);border:1px solid var(--cr3);color:var(--cr)}
.rp-tabs{display:flex;border-bottom:1px solid var(--b1);flex-shrink:0}
.rp-tab{flex:1;padding:7px 4px;text-align:center;cursor:pointer;
  font-family:var(--M);font-size:7px;letter-spacing:2px;color:var(--dim);
  border-bottom:2px solid transparent;transition:all .15s}
.rp-tab.on{color:var(--ra,var(--in));border-bottom-color:var(--ra,var(--in));background:rgba(0,0,0,.18)}
.rp-body{flex:1;overflow-y:auto;padding:12px 14px}
.rp-body::-webkit-scrollbar{width:2px}
.rp-body::-webkit-scrollbar-thumb{background:var(--b2)}
.rp-sec{font-family:var(--M);font-size:7px;letter-spacing:3px;color:var(--dim);
  display:flex;align-items:center;gap:7px;margin:13px 0 8px;text-transform:uppercase}
.rp-sec:first-child{margin-top:0}
.rp-sec::after{content:'';flex:1;height:1px;background:var(--b1)}
.mc-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.mc{background:var(--s2);border:1px solid var(--b1);border-radius:3px;padding:9px;position:relative;overflow:hidden}
.mc::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--mc,#555)}
.mc.T{--mc:#c84810}.mc.H{--mc:#0868b0}.mc.P{--mc:#5828a0}.mc.W{--mc:#a07010}
.mc.LV{--mc:#18904a}.mc.LD{--mc:#a08010}.mc.CY{--mc:#0870a0}
.mc-lbl{font-family:var(--M);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:3px}
.mc-val{font-family:var(--M);font-size:20px;font-weight:600;color:var(--mc);line-height:1}
.mc-unit{font-family:var(--M);font-size:6px;color:var(--dim);margin-top:2px}
.mc-st{display:inline-flex;align-items:center;gap:3px;margin-top:4px;
  font-family:var(--M);font-size:9px;padding:2px 5px;border-radius:1px}
.mc-st::before{content:'';width:3px;height:3px;border-radius:50%;background:currentColor;flex-shrink:0}
.mc-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.mc-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.mc-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.lk-item{display:flex;align-items:center;gap:8px;padding:7px 10px;
  background:var(--s2);border:1px solid var(--b1);border-radius:3px;margin-bottom:5px}
.lk-item:last-child{margin-bottom:0}
.lk-ico{font-size:15px;flex-shrink:0}
.lk-name{font-size:11px;font-weight:500;flex:1}
.lk-time{font-family:var(--M);font-size:7px;color:var(--dim)}
.lk-badge{font-family:var(--M);font-size:7px;padding:2px 7px;border-radius:2px;flex-shrink:0}
.lk-badge.lk{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.lk-badge.ul{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.rm-row{display:flex;align-items:center;gap:8px;padding:7px 10px;
  background:var(--s2);border:1px solid var(--b1);border-radius:3px;margin-bottom:5px;
  cursor:pointer;transition:background .12s}
.rm-row:last-child{margin-bottom:0}
.rm-row:hover{background:var(--s3);border-color:var(--b3)}
.rm-dot{width:7px;height:7px;border-radius:2px;flex-shrink:0}
.rm-info{flex:1;min-width:0}
.rm-name{font-size:11px;font-weight:500}
.rm-type{font-family:var(--M);font-size:7px;color:var(--dim);margin-top:1px}
.rm-vals{display:flex;gap:8px;align-items:center}
.rm-val{font-family:var(--M);font-size:9px}
.rm-st{font-family:var(--M);font-size:7px;padding:1px 6px;border-radius:2px;flex-shrink:0}
.rm-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.rm-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.rm-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.rm-st.sb{background:var(--in2);color:var(--in);border:1px solid var(--in3)}
.ev-row{display:flex;align-items:flex-start;gap:8px;padding:6px 0;
  border-bottom:1px solid rgba(25,36,51,.4)}
.ev-row:last-child{border:none}
.ev-t{font-family:var(--M);font-size:7px;color:var(--dim);width:34px;flex-shrink:0;padding-top:2px}
.ev-d{width:5px;height:5px;border-radius:50%;flex-shrink:0;margin-top:4px;box-shadow:0 0 4px currentColor}
.ev-d.ok{background:var(--ok);color:var(--ok)}.ev-d.wa{background:var(--wa);color:var(--wa)}
.ev-d.cr{background:var(--cr);color:var(--cr)}.ev-d.in{background:var(--in);color:var(--in)}
.toast{position:fixed;bottom:16px;left:50%;transform:translateX(-50%) translateY(60px);
  background:var(--s2);border:1px solid var(--b2);border-top:2px solid var(--in);
  border-radius:0 0 3px 3px;padding:5px 18px;font-family:var(--M);font-size:8px;
  color:var(--in);z-index:9998;white-space:nowrap;opacity:0;transition:all .28s}
.toast.show{transform:translateX(-50%) translateY(0);opacity:1}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:499;
  display:none;opacity:0;transition:opacity .28s}
@media(max-width:768px){
  :root{--HDR:48px}
  .lsb{width:250px;transform:translateX(-250px)}
  .lsb.open{transform:translateX(0)}
  .rpanel{width:100vw;--RW:100vw}
  .ham{display:flex}
  .clk{font-size:14px}
}
</style>
</head>
<body>
<canvas id="cv"></canvas>

<!-- ══════════════════════════════
     PENJELASAN THREE.JS
     ══════════════════════════════
  THREE.JS adalah library 3D berbasis WebGL yang berjalan LANGSUNG di browser.
  TIDAK perlu Blender. Semua model dibuat dari kode (geometri primitif):
  - BoxGeometry    → kotak/persegi panjang
  - CylinderGeometry → silinder/tiang
  - PlaneGeometry  → bidang datar
  - SphereGeometry → bola
  
  Cara kerja dasar:
  1. Scene    → "Panggung" tempat semua objek diletakkan
  2. Camera   → Sudut pandang (kita pakai OrthographicCamera = tanpa perspektif/isometrik)
  3. Renderer → Yang "menggambar" scene ke canvas
  4. Mesh     → Objek 3D = Geometry (bentuk) + Material (warna/tekstur)
  5. Light    → Cahaya untuk bayangan dan nuansa
  ══════════════════════════════ -->

<header class="hdr">
  <div class="logo">
    <img src="{{ asset('images/logospm.png') }}" alt="SPM" class="logo-img"
         onerror="this.style.display='none';document.querySelector('.logo-fallback').style.display='block'">
    <span class="logo-fallback">SPM OIL & GAS</span>
  </div>
  <div class="hdr-r">
    <div class="clk" id="clk">00:00:00</div>
    <button class="ham" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
  </div>
</header>

<aside class="lsb" id="lsb">
  <div class="lscroll">
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-building"></i>CONTROL ROOMS</div>
      </div>
      <div style="padding:4px 0 6px">
        <!-- Semua CR pakai warna --in (biru) sebagai identitas seragam -->
        <div class="cr-item active" id="nav-cr1" onclick="openPanel('cr1')" style="--ic:#1a8fff">
          <div class="cr-info">
            <div class="cr-name">Control Room 1</div>
            <div class="cr-sub">Cell 1 · Cell 2 · Cell 3</div>
          </div>
          <span class="cr-badge wa" id="nb-cr1">WARN</span>
        </div>
        <div class="cr-item" id="nav-cr2" onclick="openPanel('cr2')" style="--ic:#1a8fff">
          <div class="cr-info">
            <div class="cr-name">Control Room 2</div>
            <div class="cr-sub">Cell 4 · Cell 5 · Pit 1 · Pit 2</div>
          </div>
          <span class="cr-badge cr" id="nb-cr2">ALARM</span>
        </div>
        <div class="cr-item" id="nav-cr3" onclick="openPanel('cr3')" style="--ic:#1a8fff">
          <div class="cr-info">
            <div class="cr-name">Control Room 3</div>
            <div class="cr-sub">Pit 3 · Pit 4 · Pit 5 · Pit 6</div>
          </div>
          <span class="cr-badge ok" id="nb-cr3">OK</span>
        </div>
      </div>
    </div>
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-bell-ringing"></i>ALARM AKTIF</div>
        <span class="bc cr" id="al-count">0</span>
      </div>
      <div class="bb" id="al-list">
        <div style="font-family:var(--M);font-size:8px;color:var(--dim);padding:4px 0">
          Tidak ada alarm aktif
        </div>
      </div>
    </div>
  </div>
</aside>

<div class="lyr" id="lyr"></div>
<div id="tip"></div>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<div class="rpanel" id="rpanel">
  <div class="rp-accent" id="rp-accent"></div>
  <div class="rp-hdr">
    <div class="rp-hdr-row">
      <div>
        <div class="rp-type" id="rp-type">—</div>
        <div class="rp-name" id="rp-name">—</div>
        <div class="rp-zone" id="rp-zone">—</div>
      </div>
      <button class="rp-close" onclick="closePanel()"><i class="ti ti-x"></i></button>
    </div>
    <div class="rp-status ok" id="rp-sbar">
      <div class="dot" style="width:5px;height:5px;border-radius:50%;background:currentColor;animation:dp 2s infinite;flex-shrink:0"></div>
      <span id="rp-stxt">ALL SYSTEMS NOMINAL</span>
    </div>
  </div>
  <div class="rp-tabs">
    <div class="rp-tab on" id="tab-s" onclick="swTab('s')">SENSOR</div>
    <div class="rp-tab" id="tab-l" onclick="swTab('l')">LOCK</div>
    <div class="rp-tab" id="tab-r" onclick="swTab('r')">RUANGAN</div>
    <div class="rp-tab" id="tab-e" onclick="swTab('e')">LOG</div>
  </div>
  <div class="rp-body">
    <div id="pane-s"></div>
    <div id="pane-l" style="display:none"></div>
    <div id="pane-r" style="display:none"></div>
    <div id="pane-e" style="display:none"></div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
/* ══════════════════════════════════════════════════════════
   DATA (nanti diganti API/WebSocket)
══════════════════════════════════════════════════════════ */
const DATA = {
  cr1:{tp:'CONTROL ROOM',nm:'Control Room 1',zn:'TEST AREA · SECTOR A',col:'#1a8fff',
    temp:24.3,hum:58,pwr:87.2,volt:218.9,st:'WARNING',
    locks:[{name:'Main Door',st:'locked',time:'14:28'},{name:'Server Rack',st:'locked',time:'09:00'},
           {name:'Fire Exit',st:'unlocked',time:'08:30'},{name:'Cabinet A',st:'locked',time:'09:00'}],
    events:[{t:'14:32',c:'wa',m:'Temperature elevated 24.3°C'},{t:'14:28',c:'ok',m:'Access verification OK'},
            {t:'13:58',c:'ok',m:'Power stable 87.2%'},{t:'13:30',c:'in',m:'Backup sync complete'}],
    rooms:[
      {id:'cell1',tp:'TEST CELL',nm:'Test Cell 1',col:'#00c85a',st:'ACTIVE',temp:22.8,hum:54,pres:1014.0,load:75.5,cycle:'3/4'},
      {id:'cell2',tp:'TEST CELL',nm:'Test Cell 2',col:'#00c85a',st:'ACTIVE',temp:22.3,hum:53,pres:1014.2,load:72.8,cycle:'2/3'},
      {id:'cell3',tp:'TEST CELL',nm:'Test Cell 3',col:'#00c85a',st:'ACTIVE',temp:21.9,hum:53,pres:1014.3,load:70.2,cycle:'1/3'},
    ]},
  cr2:{tp:'CONTROL ROOM',nm:'Control Room 2',zn:'TEST AREA · SECTOR B',col:'#1a8fff',
    temp:26.8,hum:62,pwr:91.5,volt:217.2,st:'WARNING',
    locks:[{name:'Main Door',st:'unlocked',time:'13:42'},{name:'Server Rack',st:'locked',time:'09:00'},
           {name:'Fire Exit',st:'unlocked',time:'08:30'},{name:'Cabinet B',st:'locked',time:'09:00'}],
    events:[{t:'14:32',c:'cr',m:'TEMP EXCEEDED 26.8°C — limit 26.0°C'},{t:'13:42',c:'wa',m:'Main door unlocked by tech'},
            {t:'13:15',c:'in',m:'Humidity 62% monitoring'},{t:'12:50',c:'wa',m:'Power load high 91.5%'}],
    rooms:[
      {id:'cell4',tp:'TEST CELL',nm:'Test Cell 4',col:'#ff3c3c',st:'ACTIVE',temp:24.8,hum:58,pres:1013.2,load:82.1,cycle:'2/4'},
      {id:'cell5',tp:'TEST CELL',nm:'Test Cell 5',col:'#ff3c3c',st:'ACTIVE',temp:25.2,hum:60,pres:1012.8,load:88.4,cycle:'4/6'},
      {id:'pit1',tp:'TEST PIT',nm:'Test Pit 1',col:'#ff3c3c',st:'OPERATIONAL',temp:22.4,hum:55,pres:1014.0,depth:3.5,fluid:'EMPTY'},
      {id:'pit2',tp:'TEST PIT',nm:'Test Pit 2',col:'#ff3c3c',st:'OPERATIONAL',temp:22.9,hum:54,pres:1014.1,depth:3.5,fluid:'EMPTY'},
    ]},
  cr3:{tp:'CONTROL ROOM',nm:'Control Room 3',zn:'PHASE 1 · UPPER ZONE',col:'#1a8fff',
    temp:22.1,hum:54,pwr:74.8,volt:220.4,st:'OPERATIONAL',
    locks:[{name:'Main Door',st:'locked',time:'09:00'},{name:'Server Rack',st:'locked',time:'09:00'},
           {name:'Fire Exit',st:'locked',time:'09:00'},{name:'Cabinet C',st:'unlocked',time:'11:15'}],
    events:[{t:'14:15',c:'ok',m:'All sensors nominal'},{t:'13:50',c:'in',m:'Backup completed'},
            {t:'13:30',c:'ok',m:'Humidity 54% normal'},{t:'12:45',c:'in',m:'Temp 22.1°C logged'}],
    rooms:[
      {id:'pit3',tp:'TEST PIT',nm:'Test Pit 3',col:'#00c85a',st:'OPERATIONAL',temp:21.8,hum:52,pres:1014.5,depth:3.2,fluid:'EMPTY'},
      {id:'pit4',tp:'TEST PIT',nm:'Test Pit 4',col:'#ffaa00',st:'STANDBY',temp:23.5,hum:56,pres:1013.8,depth:2.8,fluid:'PARTIAL'},
      {id:'pit5',tp:'TEST PIT',nm:'Test Pit 5',col:'#00c85a',st:'OPERATIONAL',temp:21.2,hum:50,pres:1014.8,depth:4.2,fluid:'EMPTY'},
      {id:'pit6',tp:'TEST PIT',nm:'Test Pit 6',col:'#00c85a',st:'OPERATIONAL',temp:21.4,hum:51,pres:1014.7,depth:4.2,fluid:'EMPTY'},
    ]}
};

/* ══════════════════════════════════════════════════════════
   THREE.JS SETUP
══════════════════════════════════════════════════════════ */

/* CANVAS: elemen HTML tempat Three.js menggambar */
const cv = document.getElementById('cv');

/*
  RENDERER (WebGLRenderer):
  - Ini "mesin gambar" yang mengubah scene 3D menjadi pixel di layar
  - antialias:true → garis menjadi halus (anti-aliasing)
  - powerPreference:'high-performance' → minta GPU terbaik dari browser
*/
const renderer = new THREE.WebGLRenderer({canvas:cv, antialias:true, powerPreference:'high-performance'});
renderer.setPixelRatio(Math.min(devicePixelRatio, 2)); // max 2x untuk performa
renderer.setSize(innerWidth, innerHeight);
renderer.shadowMap.enabled = true;                     // aktifkan bayangan
renderer.shadowMap.type = THREE.PCFSoftShadowMap;      // bayangan lembut
renderer.toneMapping = THREE.ReinhardToneMapping;      // tone mapping = cara render warna
renderer.toneMappingExposure = 1.1;                    // kecerahan output

/*
  SCENE:
  - "Panggung" atau "dunia" 3D tempat semua objek, cahaya, kamera diletakkan
  - Semua yang ingin terlihat harus di-add ke scene
*/
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x060810); // warna latar belakang
scene.fog = new THREE.FogExp2(0x060810, .0053); // kabut eksponensial (makin jauh makin gelap)

/*
  CAMERA (OrthographicCamera):
  - Tampilan isometrik/ortografis = tidak ada perspektif (objek jauh tidak mengecil)
  - Parameter: left, right, top, bottom, near, far
  - Z = "zoom level" - nilai lebih kecil = lebih zoom in
*/
const Z = 22;
function getA(){ return innerWidth/innerHeight; }
const cam = new THREE.OrthographicCamera(-Z*getA(), Z*getA(), Z, -Z, .1, 500);
cam.position.set(42, 34, 42); // posisi kamera di ruang 3D (x, y, z)
cam.lookAt(2, 1, 2);           // kamera mengarah ke titik ini

/* ══════════════════════════════════════════════════════════
   LIGHTING
══════════════════════════════════════════════════════════ */

/*
  AmbientLight: Cahaya rata di seluruh scene (tidak punya arah)
  - Fungsi: membuat semua sisi objek tetap terlihat (tidak ada yang hitam pekat)
  - Parameter: warna, intensitas
*/
scene.add(new THREE.AmbientLight(0x18202e, 1.7));

/*
  DirectionalLight: Cahaya berarah seperti matahari (sinar sejajar)
  - Bisa menghasilkan bayangan (castShadow)
  - Kita set posisinya tinggi dan miring untuk efek "matahari sore"
*/
const sun = new THREE.DirectionalLight(0xfff8e8, 2.9);
sun.position.set(28, 44, 22);
sun.castShadow = true;
sun.shadow.mapSize.set(4096, 4096); // resolusi shadow map (lebih tinggi = lebih detail)
const ss = sun.shadow.camera;
ss.left = ss.bottom = -44; ss.right = ss.top = 44; // area bayangan yang dihitung
ss.near = 1; ss.far = 160;
sun.shadow.bias = -.0008; // koreksi artefak bayangan
scene.add(sun);

// Cahaya pengisi dari arah berlawanan (agar bayangan tidak terlalu gelap)
const fillLight = new THREE.DirectionalLight(0x182840, .75);
fillLight.position.set(-22, 8, -22);
scene.add(fillLight);

/*
  HemisphereLight: Cahaya langit + tanah
  - skyColor: warna dari atas (langit)
  - groundColor: warna dari bawah (pantulan tanah)
*/
scene.add(new THREE.HemisphereLight(0x18283a, 0x080e0a, .50));

/* ══════════════════════════════════════════════════════════
   MATERIAL HELPERS
   Material = "cat/bahan" permukaan objek
══════════════════════════════════════════════════════════ */

/*
  MeshLambertMaterial: Material sederhana yang merespons cahaya
  - Lebih efisien dari MeshStandardMaterial
  - Parameter: color (warna dasar), emissive (warna yang memancar sendiri), emissiveIntensity
*/
const ml = (h, e=0, ei=0) => new THREE.MeshLambertMaterial({color:h, emissive:e, emissiveIntensity:ei});

/*
  Material transparan: tambahkan transparent:true dan opacity (0=invisible, 1=opaque)
*/
const mt = (h, op, e=0, ei=0) => new THREE.MeshLambertMaterial({color:h, transparent:true, opacity:op, emissive:e, emissiveIntensity:ei});

/* ══════════════════════════════════════════════════════════
   MATERIAL BANGUNAN
══════════════════════════════════════════════════════════ */

/*
  BoxGeometry membutuhkan 6 material (1 per sisi): [right, left, top, bottom, front, back]
  Kita bisa pakai array material untuk warna berbeda per sisi
  
  GW = Green Wall → bangunan test cell/pit (hijau = OK/ACTIVE)
  Sebelum: warna cerah solid → Sekarang: gelap dengan hint hijau
*/
const GW = () => [
  ml(0x1a2a1e, 0x0a2010, .15),  // sisi kanan: sangat gelap, sedikit emit hijau
  ml(0x0e1812, 0x040e08, .1),   // sisi kiri: lebih gelap
  ml(0x223428, 0x082014, .25),  // atas: sedikit lebih terang (kena matahari)
  ml(0x050806),                  // bawah: hampir hitam (tidak kena cahaya)
  ml(0x1e2e22, 0x061810, .18),  // depan: medium
  ml(0x0e1812),                  // belakang: gelap
];

/*
  RW = Red Wall → bangunan dengan alarm/warning (merah)
  Semi transparan/gelap dengan hint merah
*/
const RW = () => [
  ml(0x2a1010, 0x1a0404, .15),
  ml(0x180a0a, 0x0e0202, .1),
  ml(0x301414, 0x1e0606, .25),
  ml(0x060404),
  ml(0x281212, 0x180404, .18),
  ml(0x180a0a),
];

/*
  Control Room materials - BARU: BIRU seragam untuk semua CR
*/
const CRW_BLUE = () => [
  ml(0x0a1828, 0x041020, .35),  // sisi kanan: biru gelap dengan emit biru
  ml(0x061018, 0x020814, .25),  // sisi kiri
  ml(0x0e2038, 0x062030, .55),  // atas: lebih terang, emit lebih kuat (kena matahari)
  ml(0x040810),                  // bawah: hitam
  ml(0x0c1e34, 0x051828, .4),   // depan: medium biru
  ml(0x061018),                  // belakang: gelap
];

// Kaca jendela - transparan biru
const GLASS   = mt(0x3870c0, .55, 0x183058, .55);
const GLASS2  = mt(0x2858a8, .42, 0x0f1e3e, .6);
// Material bingkai, beton, lampu LED, aspal
const FRAME   = ml(0x263040);
const MTL     = ml(0x1e2c3c);
const MTL2    = ml(0x2a3a50);
const CONC    = ml(0x141e2c);
const CONC2   = ml(0x0e1620);
const LED     = ml(0x0e2e18, 0x0ea040, 1.4); // LED hijau menyala
const ASPH    = ml(0x0a1018); // aspal jalan

/* ══════════════════════════════════════════════════════════
   GEOMETRY HELPERS
   Geometry = bentuk/struktur 3D (hanya data titik-titik, belum ada warna)
══════════════════════════════════════════════════════════ */
/*
  Kumpulan mesh (untuk raycasting klik)
  BODIES = array semua mesh bangunan yang bisa diklik
*/
const BODIES = [];

/*
  addm(): Helper untuk membuat dan menambahkan mesh ke scene
  - geo: THREE.BufferGeometry (bentuk)
  - mat: THREE.Material (warna/bahan)
  - x,y,z: posisi di dunia 3D
  - rx,ry,rz: rotasi dalam radian
*/
function addm(geo, mat, x, y, z, rx=0, ry=0, rz=0){
  const me = new THREE.Mesh(geo, mat);
  me.position.set(x, y, z);
  me.rotation.set(rx, ry, rz);
  me.castShadow  = true; // objek membuang bayangan
  me.receiveShadow = true; // objek menerima bayangan dari objek lain
  scene.add(me);
  return me;
}

/* Shortcut geometry */
const BOX = (w,h,d) => new THREE.BoxGeometry(w,h,d);
const CYL = (rt,rb,h,s=10) => new THREE.CylinderGeometry(rt,rb,h,s);

/*
  EdgesGeometry + LineSegments: untuk menggambar garis tepi (wireframe) sebuah box
  Digunakan untuk accent/outline bangunan
*/
function addEdge(w,h,d, x,y,z, col, op=1){
  const eg = new THREE.EdgesGeometry(BOX(w,h,d));
  const em = new THREE.LineSegments(eg,
    new THREE.LineBasicMaterial({color:col, transparent:op<1, opacity:op}));
  em.position.set(x,y,z);
  scene.add(em);
}

/* Pintu bangunan */
function addDoor(bx,bz,w,d,h,face,offAlong=0){
  const DW=0.85,DH=1.40;
  const cy=h*.15+DH/2+.22;
  let px=bx,pz=bz,ry=0;
  if(face==='front'){ pz=bz+d/2+.04; px=bx+offAlong; ry=0; }
  else if(face==='left'){ px=bx-w/2-.04; pz=bz+offAlong; ry=Math.PI/2; }
  else if(face==='right'){ px=bx+w/2+.04; pz=bz+offAlong; ry=-Math.PI/2; }
  const df=new THREE.Mesh(BOX(DW+.1,DH+.1,.08),FRAME);
  df.position.set(px,cy,pz); df.rotation.y=ry; df.castShadow=true; scene.add(df);
  const dg=new THREE.Mesh(BOX(DW,DH,.055),GLASS2);
  dg.position.set(px,cy,pz); dg.rotation.y=ry; scene.add(dg);
  const step=new THREE.Mesh(BOX(1.0,.06,.15),CONC);
  const so=face==='front'?[offAlong,0,d/2+.18]:face==='left'?[-w/2-.18,0,offAlong]:[w/2+.18,0,offAlong];
  step.position.set(bx+so[0],h*.18+.06,bz+so[2]);
  step.rotation.y=ry; step.castShadow=true; scene.add(step);
}

/* Jendela bangunan */
function addWins(bx,bz,w,d,h,face,nw=2){
  const WW=.68,WH=.54;
  const wy=h*.54;
  const sp=(face==='front'||face==='back'?w:d)/(nw+1);
  let ry=0;
  if(face==='left')ry=Math.PI/2;
  if(face==='right')ry=-Math.PI/2;
  for(let i=1;i<=nw;i++){
    const off=(i-(nw+1)/2)*sp;
    let wx=bx,wz=bz,fOff=0;
    if(face==='front'){ fOff=d/2+.04; wx=bx+off; }
    if(face==='left'){ fOff=-(w/2+.04); wz=bz+off; }
    if(face==='right'){ fOff=w/2+.04; wz=bz+off; }
    const wpx=wx+(face==='left'||face==='right'?fOff:0);
    const wpz=wz+(face==='front'?fOff:0);
    const wf=new THREE.Mesh(BOX(WW+.1,WH+.08,.07),FRAME);
    wf.position.set(wpx,wy,wpz); wf.rotation.y=ry; wf.castShadow=true; scene.add(wf);
    const wg=new THREE.Mesh(BOX(WW,WH,.05),GLASS);
    wg.position.set(wpx,wy,wpz); wg.rotation.y=ry; scene.add(wg);
    const ws=new THREE.Mesh(BOX(WW+.15,.055,.12),MTL);
    ws.position.set(wpx,wy-WH/2-.03,wpz); ws.rotation.y=ry; scene.add(ws);
    const wl=new THREE.Mesh(BOX(WW*.8,.03,.02),LED);
    wl.position.set(wpx,wy+WH*.28,wpz); wl.rotation.y=ry; scene.add(wl);
  }
}

/* ══════════════════════════════════════════════════════════
   FUNGSI UTAMA building()
   Membuat sebuah gedung lengkap dengan fondasi, dinding, atap, dll.
══════════════════════════════════════════════════════════ */
function building(id, x, z, w, d, h, mats, acol, doorCfg=null, winCfg=null, opts={}){
  const cy = h/2+.22;  // center Y (tinggi fondasi .22)

  // Fondasi beton di bawah bangunan
  addm(BOX(w+.28,.22,d+.28), CONC2, x,.11,z);

  /*
    Mesh utama bangunan:
    - BOX(w,h,d) = BoxGeometry dengan lebar, tinggi, kedalaman
    - mats = array 6 material (satu per sisi kotak)
    - userData.id = untuk identifikasi saat klik (raycasting)
  */
  const body = new THREE.Mesh(BOX(w,h,d), mats);
  body.position.set(x, cy, z);
  body.castShadow = true;
  body.receiveShadow = true;
  if(id){ body.userData.id=id; body.name=id; BODIES.push(body); }
  scene.add(body);

  // Lisplang atap (strip di atas bangunan)
  const pm = Array.isArray(mats) ? mats[2] : mats;
  addm(BOX(w+.14,.16,d+.14), pm, x, h+.28, z);
  // Atap datar tipis
  addm(BOX(w,.06,d), ml(new THREE.Color(acol).multiplyScalar(.22).getHex()), x, h+.4, z);
  // Outline bangunan (EdgesGeometry)
  addEdge(w,h,d, x,cy,z, acol, .42);

  // AC Unit di atap
  const nh = opts.hv||0;
  const hcm = ml(new THREE.Color(acol).multiplyScalar(.3).getHex());
  for(let i=0;i<nh;i++){
    const hx = (i-(nh-1)/2)*(w*.34);
    addm(BOX(w*.2,.26,d*.17), hcm, x+hx, h+.55, z+(i%2===0?d*.08:-d*.08));
    const fc = new THREE.Mesh(
      new THREE.CircleGeometry(w*.063,8),
      ml(new THREE.Color(acol).multiplyScalar(.58).getHex(), acol, .08)
    );
    fc.rotation.x = -Math.PI/2;
    fc.position.set(x+hx, h+.69, z+(i%2===0?d*.08:-d*.08));
    scene.add(fc);
  }

  if(doorCfg){ const cfg=Array.isArray(doorCfg)?doorCfg:[doorCfg]; cfg.forEach(dc=>addDoor(x,z,w,d,h,dc.face,dc.off||0)); }
  if(winCfg) addWins(x,z,w,d,h,winCfg.face,winCfg.count||2);

  // Grid garis di atap (untuk bangunan sel/pit)
  if(opts.grid){
    const gm = ml(new THREE.Color(acol).multiplyScalar(.27).getHex());
    addm(BOX(.028,.01,d-.1),gm,x,h+.43,z);
    addm(BOX(w-.1,.01,.028),gm,x,h+.43,z);
  }

  // Rak server dalam bangunan
  if(opts.rk){
    const nr=opts.rk;
    const rm=ml(new THREE.Color(acol).multiplyScalar(.1).getHex());
    for(let i=0;i<nr;i++){
      const rx=(i-(nr-1)/2)*(w/(nr+.4));
      addm(BOX(w/nr*.66,h*.8,d*.3), rm, x+rx, h*.4+.22, z+d*.1);
      for(let r=0;r<4;r++)
        addm(BOX(w/nr*.5,.03,.02), LED, x+rx, h*.17+r*(h*.18)+.22, z+d*.1+d*.15+.01);
    }
  }
  return body;
}

/* ══════════════════════════════════════════════════════════
   GROUND & INFRASTRUCTURE
══════════════════════════════════════════════════════════ */

/*
  PlaneGeometry: bidang datar 2D
  - rotation.x = -Math.PI/2 → putar 90° agar horizontal (Three.js plane default = vertikal)
*/
addm(new THREE.PlaneGeometry(150,150), ml(0x070a0e), 0,.001,0, -Math.PI/2);

// Slab beton utama (lantai area)
const slb = new THREE.Mesh(new THREE.PlaneGeometry(40,34), ml(0x0a0f14));
slb.rotation.x = -Math.PI/2;
slb.position.set(0,.01,2);
slb.receiveShadow = true;
scene.add(slb);

/*
  GridHelper: membuat grid garis membantu orientasi
  - parameter: ukuran total, jumlah divisi, warna garis utama, warna garis sub
*/
const grid = new THREE.GridHelper(0,0);
grid.position.set(0,.015,2);
scene.add(grid);

// Tembok/pagar pembatas area
[[40,.28,.32,  0,.14,-15.2],
 [40,.28,.32,  0,.14, 19.2],
 [.32,.28,34,  -20,.14,2],
 [.32,.28,34,  20,.14,2],
].forEach(([w,h,d,x,y,z])=>{
  addm(BOX(w,h,d), ml(0x1a2838), x,y,z);
  addEdge(w,h,d, x,y,z, 0x2c3e54, .55);
});

// Tangga area masuk
// addm(BOX(.45,2.6,10.5), ml(0x18202e), -5,1.3,-1.5);
// addEdge(.45,2.6,10.5, -5,1.3,-1.5, 0x2c4060, .45);
// for(let i=0;i<5;i++) addm(BOX(.34,.06,.56), CONC, -5,.3+i*.33,-5.4+i*.6);

/* ══════════════════════════════════════════════════════════
   JALAN
══════════════════════════════════════════════════════════ */

// Material jalan kuning (seperti di denah)
const roadMat  = ml(0x191b1e, 0x000000, 0);
const roadLine = ml(0xf0f0f0, 0x000000, 0);
const roadEdge = ml(0x7a7f85, 0x000000, 0);
function addRoad(x1,z1, x2,z2, width=1.0){
  /*
    Membuat segmen jalan lurus dari titik (x1,z1) ke (x2,z2)
    Kita hitung panjang dan sudut, lalu buat Box yang diputar
  */
  const dx=x2-x1, dz=z2-z1;
  const len = Math.sqrt(dx*dx+dz*dz);
  const angle = Math.atan2(dx,dz); // sudut dalam radian
  const cx=(x1+x2)/2, cz=(z1+z2)/2; // titik tengah

  // Badan jalan
  addm(BOX(width,.05,len), roadMat, cx,.04,cz, 0,angle,0);
  // Tepi kiri
  addm(BOX(.08,.08,len),   roadEdge, cx,.07,cz, 0,angle,0);
  // Garis putus-putus tengah (marking jalan)
  for(let i=0;i<Math.floor(len/1.2);i++){
    const t=(i+.5)/Math.floor(len/1.2);
    const rx=x1+dx*t, rz=z1+dz*t;
    addm(BOX(.02,.07,.45), roadLine, rx,.08,rz, 0,angle,0);
  }
}

// Jalan vertikal
addRoad(-10,-15, -10,3.5, 1.5);
addRoad(6.7,-4, 6.7,3.5, 1.5);

// Jalan horizontal
addRoad(-20,2, -10,2, 1.2);
addRoad(-10,3, 7,3, 1.2);
addRoad(7,-3.3, 19.5,-3.3, 1.2);

/* ══════════════════════════════════════════════════════════
   BANGUNAN
   Urutan: id, x, z, lebar, dalam, tinggi, materials, accent_color
══════════════════════════════════════════════════════════ */

building('pit3',-14,-9,6.3,5.5,3.0, GW(), 0x4a8460, {face:'right',off:0}, null);
building('pit4',-14,-3.4,6.3,5.4,3.0, RW(), 0xa83848, {face:'right',off:0}, null);
building('cr3',-5.5,-11,4.0,2.0,2.0, CRW_BLUE(), 0x1a8fff, {face:'front',off:0.7}, {face:'front',count:2});

building('cell5',1.8,-10,6.5,6,3.0, GW(), 0xa83848, {face:'right',off:2}, null);
building('cell4',1.8,-3.8,6.5,6,3.0, RW(), 0xa83848,{face:'front',off:2.2}, null);

building('pit1',10.8,-9,4.5,7,3.0, RW(), 0xa83848,{face:'front',off:-1.3}, null);
building('pit2',15.5,-9,4.5,7,3.0, RW(), 0xa83848,{face:'front',off:1.3}, null);

building('cr1',10.5,1.5,4.0,2.0,2.0, CRW_BLUE(), 0x1a8fff,{face:'front',off:0.7}, {face:'front',count:2});
building('cr2',15.5,1.5,4.0,2.0,2.0, CRW_BLUE(), 0x1a8fff,{face:'front',off:0.7}, {face:'front',count:2});

building('pit5',-12.3,11,5.8,6.5,3.0, GW(), 0x4a8460, null, null);
building('pit6',-6.2,11,5.8,6.5,3.0, GW(), 0x4a8460, null, null);

building('cell3',1.8,13,6.7,6.1,3.0, GW(), 0x4a8460, null, null);
building('cell2', 8.6,13,6.7,6.1,3.0, GW(), 0x4a8460, null, null);
building('cell1',15.5,13,6.7,6.1,3.0, GW(), 0x4a8460, null, null);

/* ══════════════════════════════════════════════════════════
   LABEL 3D (HTML overlay di atas canvas)
   Teknik: proyeksikan posisi 3D ke koordinat layar 2D
══════════════════════════════════════════════════════════ */
const LBLS = [
  {id:'cr3',  p:new THREE.Vector3(-5.6,2.0,-9.5),  t:'CONTROL ROOM 3', cls:'lbl-r'},
  {id:'cr1',  p:new THREE.Vector3(9.5,4.8,-3),   t:'CONTROL ROOM 1', cls:'lbl-r'},
  {id:'cr2',  p:new THREE.Vector3(15,4.8,-3),    t:'CONTROL ROOM 2', cls:'lbl-r'},
  {id:'pit3', p:new THREE.Vector3(-14,3.0,-10),   t:'TEST PIT 3',  cls:'lbl-r'},
  {id:'pit4', p:new THREE.Vector3(-14,3.0,-4),   t:'TEST PIT 4',  cls:'lbl-r'},
  {id:'cell5',p:new THREE.Vector3(1.5,2.7,-7.5), t:'TEST CELL 5', cls:'lbl-r'},
  {id:'cell4',p:new THREE.Vector3(1.5,2.7,-1.2), t:'TEST CELL 4', cls:'lbl-r'},
  {id:'pit1', p:new THREE.Vector3(9.5,2.3,-8.5), t:'TEST PIT 1',  cls:'lbl-r'},
  {id:'pit2', p:new THREE.Vector3(15,2.3,-8.5),  t:'TEST PIT 2',  cls:'lbl-r'},
  {id:'pit5', p:new THREE.Vector3(-15,2.3,8.5),  t:'TEST PIT 5',  cls:'lbl-r'},
  {id:'pit6', p:new THREE.Vector3(-9,2.3,8.5),   t:'TEST PIT 6',  cls:'lbl-r'},
  {id:'cell3',p:new THREE.Vector3(-2,2.3,8.5),   t:'TEST CELL 3', cls:'lbl-r'},
  {id:'cell2',p:new THREE.Vector3(4.5,2.3,8.5),  t:'TEST CELL 2', cls:'lbl-r'},
  {id:'cell1',p:new THREE.Vector3(12,2.3,8.5),   t:'TEST CELL 1', cls:'lbl-r'},
];
const lyrEl = document.getElementById('lyr');
LBLS.forEach(lb=>{
  const div=document.createElement('div');
  div.className='lbl '+lb.cls;
  if(Array.isArray(lb.t)){
    div.innerHTML=`<div style="color:${lb.col};font-size:10px;font-family:var(--FC);font-weight:700">${lb.t[0]}</div>
      <div style="font-size:7px;font-family:var(--M);color:rgba(170,210,255,.8);letter-spacing:.8px">${lb.t[1]}</div>`;
  } else {
    div.textContent = lb.t;
  }
  if(lb.id){
    div.style.cursor='pointer'; div.style.pointerEvents='auto';
    div.addEventListener('click',()=>{ const crId=findCR(lb.id); openPanel(crId||lb.id); });
  }
  lyrEl.appendChild(div);
  lb.el = div;
});

function findCR(id){
  for(const [crId,d] of Object.entries(DATA)){
    if(crId===id) return crId;
    if(d.rooms.find(r=>r.id===id)) return crId;
  }
  return null;
}

/* ══════════════════════════════════════════════════════════
   RAYCASTING - Deteksi klik pada objek 3D
   
   Cara kerja: kirim "sinar" dari posisi mouse menembus scene
   → cek objek mana yang berpotongan dengan sinar tsb
══════════════════════════════════════════════════════════ */
const RC = new THREE.Raycaster();
const MV = new THREE.Vector2(); // posisi mouse dalam koordinat normalized (-1 to 1)
const tipEl = document.getElementById('tip');
let hov=null, mdx=0, mdy=0, curId=null, lv=null;

cv.addEventListener('mousemove', e=>{
  // Konversi pixel screen → koordinat normalized WebGL
  MV.x = (e.clientX/innerWidth)*2-1;
  MV.y = -((e.clientY/innerHeight)*2-1);
  RC.setFromCamera(MV, cam);
  const hits = RC.intersectObjects(BODIES, false); // false = tidak rekursif
  if(hits.length){
    const id = hits[0].object.userData.id;
    const crId = findCR(id);
    const d = DATA[crId]||null;
    if(d){
      hov=id; cv.style.cursor='pointer';
      tipEl.style.borderTopColor=d.col; tipEl.style.color=d.col;
      const roomD = Object.values(DATA).flatMap(x=>x.rooms).find(r=>r.id===id);
      const label = roomD?`${roomD.nm} — dimonitor ${d.nm}`:d.nm;
      tipEl.textContent=`${label} · Klik untuk detail`;
      tipEl.style.left=(e.clientX+14)+'px'; tipEl.style.top=(e.clientY-14)+'px';
      tipEl.style.opacity='1';
    }
  } else { hov=null; cv.style.cursor=''; tipEl.style.opacity='0'; }
});
cv.addEventListener('mousedown',e=>{mdx=e.clientX;mdy=e.clientY;});
cv.addEventListener('click',e=>{
  if(Math.abs(e.clientX-mdx)>6||Math.abs(e.clientY-mdy)>6)return;
  MV.x=(e.clientX/innerWidth)*2-1;
  MV.y=-((e.clientY/innerHeight)*2-1);
  RC.setFromCamera(MV,cam);
  const hits=RC.intersectObjects(BODIES,false);
  if(hits.length){ const crId=findCR(hits[0].object.userData.id); if(crId)openPanel(crId); }
});
cv.addEventListener('touchend',e=>{
  const t=e.changedTouches[0];
  MV.x=(t.clientX/innerWidth)*2-1;
  MV.y=-((t.clientY/innerHeight)*2-1);
  RC.setFromCamera(MV,cam);
  const hits=RC.intersectObjects(BODIES,false);
  if(hits.length){ const crId=findCR(hits[0].object.userData.id); if(crId)openPanel(crId); }
});

/* ══════════════════════════════════════════════════════════
   RIGHT PANEL LOGIC
══════════════════════════════════════════════════════════ */
function openPanel(id){
  if(!DATA[id])return;
  curId=id;
  const d=DATA[id];
  const rp=document.getElementById('rpanel');
  // Warna panel tetap biru untuk CR
  const panelCol = '#1a8fff';
  document.getElementById('rp-accent').style.background=panelCol;
  rp.style.setProperty('--ra',panelCol);
  document.getElementById('rp-type').textContent=d.tp;
  document.getElementById('rp-name').textContent=d.nm;
  document.getElementById('rp-name').style.color=panelCol;
  document.getElementById('rp-zone').textContent=d.zn;
  updatePanelStatus(id);
  renderSensor(id); renderLock(id); renderRooms(id); renderEvents(id);
  swTab('s');
  rp.classList.add('open');
  ['cr1','cr2','cr3'].forEach(k=>{
    document.getElementById('nav-'+k).classList.toggle('active',k===id);
  });
  if(window.innerWidth<=768) closeSidebar();
  if(lv) clearInterval(lv);
  lv=setInterval(()=>{ if(!curId)return; tickRoom(curId); renderSensor(curId); updatePanelStatus(curId); },3200);
}

function updatePanelStatus(id){
  const d=DATA[id];
  const warn=d.temp>26||(d.hum&&d.hum>60)||d.st==='WARNING';
  const alarm=d.temp>26.5;
  const sb=document.getElementById('rp-sbar');
  sb.className='rp-status '+(alarm?'cr':warn?'wa':'ok');
  document.getElementById('rp-stxt').textContent=
    alarm?'⚠ ALARM — SEGERA PERIKSA':warn?'⚠ PARAMETER DI LUAR BATAS':'● SEMUA SISTEM NORMAL';
}

function closePanel(){
  document.getElementById('rpanel').classList.remove('open');
  curId=null; if(lv){clearInterval(lv);lv=null;}
  ['cr1','cr2','cr3'].forEach(k=>document.getElementById('nav-'+k).classList.remove('active'));
}

function renderSensor(id){
  const d=DATA[id];
  const tH=d.temp>26,hH=d.hum>60,pW=d.pwr>90;
  document.getElementById('pane-s').innerHTML=`
  <div class="rp-sec">SENSOR CONTROL ROOM</div>
  <div class="mc-grid">
    <div class="mc T">
      <div class="mc-lbl">TEMPERATURE</div>
      <div>${d.temp} °C</div>
      <div class="mc-st ${tH?'wa':'ok'}">${tH?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc H">
      <div class="mc-lbl">HUMIDITY</div>
      <div>${d.hum} % RH</div>
      <div class="mc-st ${hH?'wa':'ok'}">${hH?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc W">
      <div class="mc-lbl">POWER LOAD</div>
      <div>${d.pwr} %</div>
      <div class="mc-st ${pW?'wa':'ok'}">${pW?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc P">
      <div class="mc-lbl">VOLTAGE</div>
      <div>${d.volt} V AC</div>
      <div class="mc-st ok">STABIL</div>
    </div>
  </div>
  <div style="margin-top:8px;font-family:var(--M);font-size:10px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString('en-GB')}</span>
  </div>`;
}

function renderLock(id){
  const d=DATA[id];
  const locked=d.locks.filter(l=>l.st==='locked').length;
  const total=d.locks.length;
  const pct=Math.round(locked/total*100);
  const sc=locked<total?'var(--wa)':'var(--ok)';
  let h=`
  <div class="rp-sec">LOCK SYSTEM</div>
  <div style="background:var(--s2);border:1px solid var(--b1);border-radius:3px;
    padding:10px 12px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
    <div>
      <div style="font-family:var(--M);font-size:7px;color:var(--txt);letter-spacing:2px;margin-bottom:3px">TERKUNCI</div>
      <div style="font-family:var(--M);font-size:22px;font-weight:600;color:${sc}">${locked}<span style="font-size:9px;color:var(--dim)"> / ${total}</span></div>
    </div>
    <div style="text-align:right">
      <div style="font-family:var(--M);font-size:7px;color:var(--dim);margin-bottom:5px">KEAMANAN</div>
      <div style="width:80px;height:4px;background:var(--b1);border-radius:2px;overflow:hidden;margin-bottom:4px">
        <div style="height:100%;width:${pct}%;background:${sc};border-radius:2px;transition:width .8s"></div>
      </div>
      <div style="font-family:var(--M);font-size:9px;color:${sc}">${pct}%</div>
    </div>
  </div>`;
  d.locks.forEach(l=>{
    h+=`<div class="lk-item">
      <i class="ti ${l.st==='locked'?'ti-lock':'ti-lock-open'} lk-ico"
         style="color:${l.st==='locked'?'var(--ok)':'var(--cr)'}"></i>
      <div style="flex:1">
        <div class="lk-name">${l.name}</div>
        <div class="lk-time">${l.time}</div>
      </div>
      <div class="lk-badge ${l.st==='locked'?'lk':'ul'}">${l.st==='locked'?'LOCKED':'OPEN'}</div>
    </div>`;
  });
  document.getElementById('pane-l').innerHTML=h;
}

function renderRooms(id){
  const d=DATA[id];
  let h=`<div class="rp-sec">RUANGAN YANG DIMONITOR (${d.rooms.length})</div>`;
  d.rooms.forEach(r=>{
    const isCell=r.tp==='TEST CELL',isPit=r.tp==='TEST PIT';
    const tW=r.temp>25;
    const stCl=r.st==='ACTIVE'||r.st==='OPERATIONAL'?'ok':r.st==='STANDBY'?'sb':'wa';
    const metaVal=isCell?`${r.load}% · ${r.cycle}`:isPit?`${r.depth}m · ${r.fluid}`:'—';
    h+=`<div class="rm-row">
      <div class="rm-dot" style="background:${r.col}"></div>
      <div class="rm-info">
        <div class="rm-name">${r.nm}</div>
        <div class="rm-type">${r.tp}</div>
      </div>
      <div class="rm-vals">
        <div class="rm-val" style="color:${tW?'var(--wa)':'var(--txt)'}">${r.temp}°C</div>
        <div class="rm-val" style="color:var(--dim)">${r.hum}%</div>
        <div class="rm-val" style="color:var(--dim);font-size:8px">${metaVal}</div>
      </div>
      <div class="rm-st ${stCl}">${r.st}</div>
    </div>`;
  });
  document.getElementById('pane-r').innerHTML=h;
}

function renderEvents(id){
  const d=DATA[id];
  let h=`<div class="rp-sec">LOG TERBARU</div>`;
  d.events.forEach(e=>{
    h+=`<div class="ev-row">
      <div class="ev-t">${e.t}</div>
      <div class="ev-d ${e.c}"></div>
      <div class="ev-m">${e.m}</div>
    </div>`;
  });
  document.getElementById('pane-e').innerHTML=h;
}

function swTab(n){
  ['s','l','r','e'].forEach(t=>{
    document.getElementById('tab-'+t).classList.remove('on');
    document.getElementById('pane-'+t).style.display='none';
  });
  document.getElementById('tab-'+n).classList.add('on');
  document.getElementById('pane-'+n).style.display='block';
}

/* ══════════════════════════════════════════════════════════
   ALARM & SIDEBAR
══════════════════════════════════════════════════════════ */
function getAlarms(id){
  const d=DATA[id];const al=[];
  if(d.temp>26) al.push({lv:'cr',nm:`${d.nm}: Suhu ${d.temp}°C`,sub:'Melewati batas 26°C',id,time:getNow()});
  else if(d.temp>24) al.push({lv:'wa',nm:`${d.nm}: Suhu ${d.temp}°C`,sub:'Mendekati batas',id,time:getNow()});
  if(d.hum>60) al.push({lv:'wa',nm:`${d.nm}: Kelembaban ${d.hum}%`,sub:'Di atas normal',id,time:getNow()});
  if(d.pwr>90) al.push({lv:'wa',nm:`${d.nm}: Daya ${d.pwr}%`,sub:'Beban tinggi',id,time:getNow()});
  d.locks.filter(l=>l.st==='unlocked').forEach(l=>
    al.push({lv:'wa',nm:`${d.nm}: ${l.name}`,sub:'Tidak terkunci',id,time:l.time}));
  return al;
}
function getNow(){ return new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}); }

function updateAlarmSidebar(){
  const allAl=[...getAlarms('cr1'),...getAlarms('cr2'),...getAlarms('cr3')];
  const count=allAl.length;
  document.getElementById('al-count').textContent=count;
  document.getElementById('al-count').className='bc '+(count>0?allAl.some(a=>a.lv==='cr')?'cr':'wa':'ok');
  const list=document.getElementById('al-list');
  if(!count){
    list.innerHTML=`<div style="font-family:var(--M);font-size:8px;color:var(--dim);padding:4px 0;display:flex;align-items:center;gap:6px">
      <i class="ti ti-circle-check" style="color:var(--ok);font-size:12px"></i>Tidak ada alarm</div>`;
  } else {
    list.innerHTML=allAl.map(a=>`
      <div class="al-row" onclick="openPanel('${a.id}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt">
          <div class="al-name">${a.nm}</div>
          <div class="al-sub">${a.sub}</div>
        </div>
        <div class="al-time">${a.time}</div>
      </div>`).join('');
  }
  ['cr1','cr2','cr3'].forEach(k=>{
    const al=getAlarms(k);
    const hasCr=al.some(a=>a.lv==='cr'),hasWa=al.some(a=>a.lv==='wa');
    const badge=document.getElementById('nb-'+k);
    if(hasCr){badge.className='cr-badge cr';badge.textContent='ALARM';}
    else if(hasWa){badge.className='cr-badge wa';badge.textContent='WARN';}
    else{badge.className='cr-badge ok';badge.textContent='OK';}
  });
}

/* ══════════════════════════════════════════════════════════
   LIVE SIMULATION (ganti dengan fetch/WebSocket nanti)
══════════════════════════════════════════════════════════ */
function tickRoom(id){
  const d=DATA[id];
  d.temp=+(d.temp+(Math.random()-.5)*.25).toFixed(1);
  d.hum=Math.max(40,Math.min(76,Math.round(d.hum+(Math.random()-.5))));
  d.pwr=+(d.pwr+(Math.random()-.5)*.4).toFixed(1);
  d.rooms.forEach(r=>{
    r.temp=+(r.temp+(Math.random()-.5)*.2).toFixed(1);
    r.hum=Math.max(40,Math.min(76,Math.round(r.hum+(Math.random()-.5))));
    if(r.load!=null) r.load=+(r.load+(Math.random()-.5)*.5).toFixed(1);
  });
}
setInterval(()=>{
  ['cr1','cr2','cr3'].forEach(tickRoom);
  updateAlarmSidebar();
  if(curId){ renderSensor(curId); renderRooms(curId); }
},4000);

/* Mobile sidebar */
function toggleSidebar(){
  const sb=document.getElementById('lsb');
  const ov=document.getElementById('overlay');
  const open=sb.classList.toggle('open');
  ov.style.display='block';
  setTimeout(()=>ov.style.opacity=open?'1':'0',10);
  if(!open)setTimeout(()=>ov.style.display='none',300);
}
function closeSidebar(){
  document.getElementById('lsb').classList.remove('open');
  const ov=document.getElementById('overlay');
  ov.style.opacity='0';
  setTimeout(()=>ov.style.display='none',300);
}

/* Clock */
(function tick(){
  const n=new Date(),p=v=>String(v).padStart(2,'0');
  document.getElementById('clk').textContent=`${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
  setTimeout(tick,1000);
})();

/* ══════════════════════════════════════════════════════════
   ANIMATION LOOP - Jantung Three.js
   
   requestAnimationFrame: meminta browser memanggil fungsi ini
   sebelum setiap frame render (~60fps)
   Ini adalah "game loop" Three.js
══════════════════════════════════════════════════════════ */
const TMP = new THREE.Vector3();
let T = 0;

function animate(){
  requestAnimationFrame(animate); // jadwalkan frame berikutnya
  T += 0.016; // increment waktu (~1/60 detik per frame)

  // Highlight bangunan saat hover/selected
  BODIES.forEach(b=>{
    const isH  = hov===b.userData.id;
    const isSel= curId&&DATA[curId]&&(
      b.userData.id===curId||
      DATA[curId].rooms.find(r=>r.id===b.userData.id)
    );
    const ms = Array.isArray(b.material)?b.material:[b.material];
    ms.forEach(m=>{
      // simpan emissiveIntensity awal
      if(m.emissive&&m._base===undefined) m._base=m.emissiveIntensity;
      if(m.emissive){
        if(isSel)      m.emissiveIntensity=(m._base||0)+.28+Math.sin(T*3)*.1;
        else if(isH)   m.emissiveIntensity=(m._base||0)+.18;
        else           m.emissiveIntensity=m._base||0;
      }
    });
  });

  /*
    Proyeksi label 3D → layar 2D:
    Vector3.project(camera) mengubah koordinat 3D world
    menjadi koordinat 2D normalized (-1 to 1)
    Kita konversi ke pixel layar dengan:
    x_pixel = (x_norm * 0.5 + 0.5) * innerWidth
    y_pixel = (-y_norm * 0.5 + 0.5) * innerHeight
  */
  LBLS.forEach(lb=>{
    TMP.copy(lb.p).project(cam);
    lb.el.style.left = ((TMP.x*.5+.5)*innerWidth)+'px';
    lb.el.style.top  = ((-.5*TMP.y+.5)*innerHeight)+'px';
    lb.el.style.opacity = TMP.z<1?'1':'0'; // sembunyikan jika di belakang kamera
  });

  /*
    renderer.render(scene, camera):
    Ini titik akhir - menggambar seluruh scene dari sudut pandang kamera
    ke canvas HTML
  */
  renderer.render(scene, cam);
}
animate();

/* Resize handler */
window.addEventListener('resize',()=>{
  const A=getA();
  cam.left=-Z*A; cam.right=Z*A; cam.top=Z; cam.bottom=-Z;
  cam.updateProjectionMatrix(); // WAJIB dipanggil setelah ubah parameter kamera
  renderer.setSize(innerWidth, innerHeight);
});

/* Init */
updateAlarmSidebar();
</script>
</body>
</html>