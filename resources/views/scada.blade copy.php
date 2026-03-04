<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panduan Three.js — SPM Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0d1117;
      --bg2: #161b22;
      --bg3: #1c2330;
      --border: #30363d;
      --txt: #e6edf3;
      --dim: #7d8590;
      --blue: #58a6ff;
      --green: #3fb950;
      --orange: #d29922;
      --red: #f85149;
      --purple: #bc8cff;
      --cyan: #79c0ff;
      --yellow: #e3b341;
      --pink: #ff7b72;
      --code-bg: #0d1117;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--bg);
      color: var(--txt);
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      line-height: 1.7;
    }

    /* ── LAYOUT ── */
    .shell {
      display: flex;
      min-height: 100vh;
    }

    /* ── SIDEBAR NAV ── */
    nav {
      width: 260px;
      background: var(--bg2);
      border-right: 1px solid var(--border);
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      padding: 20px 0;
      z-index: 100;
    }

    nav::-webkit-scrollbar {
      width: 3px;
    }

    nav::-webkit-scrollbar-thumb {
      background: var(--border);
    }

    .nav-logo {
      padding: 0 18px 20px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 14px;
    }

    .nav-logo h1 {
      font-size: 13px;
      font-weight: 700;
      color: var(--blue);
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .nav-logo p {
      font-size: 10px;
      color: var(--dim);
      margin-top: 3px;
      font-family: 'IBM Plex Mono', monospace;
    }

    .nav-group-label {
      font-size: 9px;
      font-weight: 700;
      letter-spacing: 2px;
      color: var(--dim);
      text-transform: uppercase;
      padding: 10px 18px 5px;
    }

    nav a {
      display: block;
      padding: 6px 18px;
      color: var(--dim);
      text-decoration: none;
      font-size: 12.5px;
      border-left: 2px solid transparent;
      transition: all .15s;
    }

    nav a:hover {
      color: var(--txt);
      background: rgba(88, 166, 255, .06);
    }

    nav a.active {
      color: var(--blue);
      border-left-color: var(--blue);
      background: rgba(88, 166, 255, .08);
    }

    nav a .num {
      display: inline-block;
      background: var(--bg3);
      border: 1px solid var(--border);
      color: var(--dim);
      font-size: 9px;
      padding: 1px 5px;
      border-radius: 3px;
      margin-right: 6px;
      font-family: 'IBM Plex Mono', monospace;
      min-width: 22px;
      text-align: center;
    }

    /* ── MAIN CONTENT ── */
    main {
      margin-left: 260px;
      flex: 1;
      max-width: 860px;
      padding: 40px 48px 80px;
    }

    /* ── HERO ── */
    .hero {
      background: linear-gradient(135deg, #0e1c2e 0%, #0d1117 100%);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 32px 36px;
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--blue), var(--purple), var(--cyan));
    }

    .hero h1 {
      font-size: 22px;
      font-weight: 700;
      color: var(--txt);
      margin-bottom: 8px;
    }

    .hero p {
      color: var(--dim);
      font-size: 13px;
      max-width: 540px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(88, 166, 255, .1);
      border: 1px solid rgba(88, 166, 255, .25);
      color: var(--blue);
      font-size: 11px;
      font-family: 'IBM Plex Mono', monospace;
      padding: 3px 9px;
      border-radius: 20px;
      margin-top: 14px;
      margin-right: 6px;
    }

    .badge.green {
      background: rgba(63, 185, 80, .1);
      border-color: rgba(63, 185, 80, .25);
      color: var(--green);
    }

    .badge.orange {
      background: rgba(210, 153, 34, .1);
      border-color: rgba(210, 153, 34, .25);
      color: var(--orange);
    }

    /* ── SECTION ── */
    section {
      margin-bottom: 52px;
      scroll-margin-top: 24px;
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border);
    }

    .section-num {
      background: var(--blue);
      color: #000;
      font-size: 10px;
      font-weight: 700;
      font-family: 'IBM Plex Mono', monospace;
      width: 24px;
      height: 24px;
      border-radius: 6px;
      display: grid;
      place-items: center;
      flex-shrink: 0;
    }

    .section-num.green {
      background: var(--green);
    }

    .section-num.orange {
      background: var(--orange);
    }

    .section-num.purple {
      background: var(--purple);
    }

    .section-num.red {
      background: var(--red);
    }

    .section-num.cyan {
      background: var(--cyan);
    }

    .section-num.yellow {
      background: var(--yellow);
    }

    .section-num.pink {
      background: var(--pink);
    }

    h2 {
      font-size: 17px;
      font-weight: 700;
      color: var(--txt);
    }

    h3 {
      font-size: 13px;
      font-weight: 700;
      color: var(--cyan);
      margin: 22px 0 8px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    p {
      color: var(--dim);
      font-size: 13px;
      margin-bottom: 12px;
    }

    /* ── CODE BLOCK ── */
    .code-wrap {
      background: var(--code-bg);
      border: 1px solid var(--border);
      border-radius: 8px;
      margin: 14px 0 18px;
      overflow: hidden;
    }

    .code-header {
      background: var(--bg2);
      border-bottom: 1px solid var(--border);
      padding: 6px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .code-lang {
      font-size: 10px;
      font-family: 'IBM Plex Mono', monospace;
      color: var(--dim);
      letter-spacing: 1px;
    }

    .copy-btn {
      background: none;
      border: 1px solid var(--border);
      color: var(--dim);
      font-size: 10px;
      font-family: 'IBM Plex Mono', monospace;
      padding: 2px 8px;
      border-radius: 4px;
      cursor: pointer;
      transition: all .15s;
    }

    .copy-btn:hover {
      border-color: var(--blue);
      color: var(--blue);
    }

    pre {
      padding: 16px 18px;
      overflow-x: auto;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 12.5px;
      line-height: 1.75;
    }

    pre::-webkit-scrollbar {
      height: 3px;
    }

    pre::-webkit-scrollbar-thumb {
      background: var(--border);
    }

    /* syntax highlight */
    .kw {
      color: #ff7b72;
    }

    /* keyword: const, new, function */
    .fn {
      color: #d2a8ff;
    }

    /* function name */
    .str {
      color: #a5d6ff;
    }

    /* string */
    .num {
      color: #79c0ff;
    }

    /* number */
    .cm {
      color: #8b949e;
      font-style: italic;
    }

    /* comment */
    .cls {
      color: #ffa657;
    }

    /* class: THREE.xxx */
    .prp {
      color: #e6edf3;
    }

    /* property */
    .op {
      color: #ff7b72;
    }

    /* operator */
    /* ── CALLOUT ── */
    .callout {
      display: flex;
      gap: 12px;
      padding: 13px 16px;
      border-radius: 7px;
      margin: 14px 0;
      font-size: 13px;
    }

    .callout.info {
      background: rgba(88, 166, 255, .07);
      border: 1px solid rgba(88, 166, 255, .2);
      color: var(--cyan);
    }

    .callout.tip {
      background: rgba(63, 185, 80, .07);
      border: 1px solid rgba(63, 185, 80, .2);
      color: var(--green);
    }

    .callout.warn {
      background: rgba(210, 153, 34, .07);
      border: 1px solid rgba(210, 153, 34, .2);
      color: var(--orange);
    }

    .callout.danger {
      background: rgba(248, 81, 73, .07);
      border: 1px solid rgba(248, 81, 73, .2);
      color: var(--red);
    }

    .callout-icon {
      font-size: 16px;
      flex-shrink: 0;
    }

    /* ── TABLE ── */
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 14px 0 18px;
      font-size: 12.5px;
    }

    th {
      background: var(--bg2);
      border: 1px solid var(--border);
      padding: 8px 12px;
      text-align: left;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 10px;
      letter-spacing: 1px;
      color: var(--blue);
    }

    td {
      border: 1px solid var(--border);
      padding: 8px 12px;
      color: var(--dim);
      vertical-align: top;
    }

    td code {
      background: var(--bg3);
      padding: 1px 5px;
      border-radius: 3px;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 11px;
      color: var(--orange);
    }

    tr:hover td {
      background: rgba(255, 255, 255, .02);
    }

    /* ── INLINE ── */
    code {
      background: var(--bg3);
      border: 1px solid var(--border);
      padding: 1px 6px;
      border-radius: 4px;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 11.5px;
      color: var(--orange);
    }

    /* ── DIAGRAM ── */
    .diagram {
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 20px;
      margin: 14px 0 18px;
      text-align: center;
    }

    .dia-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin: 6px 0;
      flex-wrap: wrap;
    }

    .dia-box {
      background: var(--bg3);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 8px 14px;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 11px;
      min-width: 90px;
    }

    .dia-box.blue {
      border-color: var(--blue);
      color: var(--blue);
    }

    .dia-box.green {
      border-color: var(--green);
      color: var(--green);
    }

    .dia-box.orange {
      border-color: var(--orange);
      color: var(--orange);
    }

    .dia-box.purple {
      border-color: var(--purple);
      color: var(--purple);
    }

    .dia-box.cyan {
      border-color: var(--cyan);
      color: var(--cyan);
    }

    .dia-arrow {
      color: var(--dim);
      font-size: 18px;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      nav {
        width: 100%;
        height: auto;
        position: relative;
        border-right: none;
        border-bottom: 1px solid var(--border);
      }

      main {
        margin-left: 0;
        padding: 20px;
      }

      .shell {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <div class="shell">

    <!-- ══════ SIDEBAR ══════ -->
    <nav id="sidebar">
      <div class="nav-logo">
        <h1>Three.js Guide</h1>
        <p>SPM Oil & Gas Dashboard</p>
      </div>

      <div class="nav-group-label">Konsep Dasar</div>
      <a href="#s1" class="active"><span class="num">01</span> Arsitektur Three.js</a>
      <a href="#s2"><span class="num">02</span> Renderer & Canvas</a>
      <a href="#s3"><span class="num">03</span> Scene & Fog</a>
      <a href="#s4"><span class="num">04</span> Camera</a>

      <div class="nav-group-label">Objek 3D</div>
      <a href="#s5"><span class="num">05</span> Geometry (Bentuk)</a>
      <a href="#s6"><span class="num">06</span> Material (Permukaan)</a>
      <a href="#s7"><span class="num">07</span> Mesh = Geo + Mat</a>
      <a href="#s8"><span class="num">08</span> Posisi & Rotasi</a>

      <div class="nav-group-label">Cahaya & Warna</div>
      <a href="#s9"><span class="num">09</span> Jenis-jenis Light</a>
      <a href="#s10"><span class="num">10</span> Warna & THREE.Color</a>
      <a href="#s11"><span class="num">11</span> Bayangan (Shadow)</a>

      <div class="nav-group-label">Fitur Lanjutan</div>
      <a href="#s12"><span class="num">12</span> Raycasting (Klik)</a>
      <a href="#s13"><span class="num">13</span> Animation Loop</a>
      <a href="#s14"><span class="num">14</span> Label 3D → 2D</a>
      <a href="#s15"><span class="num">15</span> Fungsi building()</a>

      <div class="nav-group-label">Kustomisasi</div>
      <a href="#s16"><span class="num">16</span> Ubah Warna Gedung</a>
      <a href="#s17"><span class="num">17</span> Tambah Bangunan</a>
      <a href="#s18"><span class="num">18</span> Membuat Jalan</a>
    </nav>

    <!-- ══════ MAIN ══════ -->
    <main>

      <!-- HERO -->
      <div class="hero">
        <h1>Panduan Lengkap Three.js</h1>
        <p>Referensi sintaks dari kode SPM Oil & Gas Dashboard. Semua model 3D dibuat murni dari kode JavaScript — tidak perlu Blender atau software 3D lainnya.</p>
        <span class="badge">Three.js r128</span>
        <span class="badge green">WebGL</span>
        <span class="badge orange">Isometric View</span>
      </div>

      <!-- ═══ 01 ARSITEKTUR ═══ -->
      <section id="s1">
        <div class="section-title">
          <div class="section-num">01</div>
          <h2>Arsitektur Three.js — Gambaran Besar</h2>
        </div>

        <p>Three.js bekerja seperti studio film animasi. Ada 4 komponen utama yang selalu ada di setiap proyek:</p>

        <div class="diagram">
          <div class="dia-row">
            <div class="dia-box blue">SCENE<br><small style="color:var(--dim);font-size:9px">Panggung / Dunia 3D</small></div>
            <div class="dia-arrow">+</div>
            <div class="dia-box green">CAMERA<br><small style="color:var(--dim);font-size:9px">Sudut Pandang</small></div>
            <div class="dia-arrow">+</div>
            <div class="dia-box orange">OBJECTS<br><small style="color:var(--dim);font-size:9px">Mesh + Light</small></div>
            <div class="dia-arrow">→</div>
            <div class="dia-box purple">RENDERER<br><small style="color:var(--dim);font-size:9px">Output ke Canvas</small></div>
          </div>
          <div style="margin-top:12px;font-size:11px;color:var(--dim);font-family:'IBM Plex Mono',monospace">
            renderer.render(scene, camera) → gambar ke layar
          </div>
        </div>

        <div class="callout info">
          <span class="callout-icon">💡</span>
          <div><strong>Analogi sederhana:</strong> Scene = set film. Camera = kamera film. Objects = aktor & properti. Renderer = mesin film yang merekam dan menampilkan hasilnya ke layar.</div>
        </div>

        <p>Alur kerja dasar yang selalu berulang setiap frame (~60 kali per detik):</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — ALUR DASAR</span></div>
          <pre><span class="cm">// 1. Buat "panggung"</span>
<span class="kw">const</span> scene <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Scene</span>();

<span class="cm">// 2. Buat kamera (sudut pandang)</span>
<span class="kw">const</span> camera <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.OrthographicCamera</span>(...);

<span class="cm">// 3. Buat objek → tambahkan ke scene</span>
<span class="kw">const</span> geometry <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.BoxGeometry</span>(<span class="num">1</span>, <span class="num">1</span>, <span class="num">1</span>);
<span class="kw">const</span> material  <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.MeshLambertMaterial</span>({ color: <span class="num">0xff0000</span> });
<span class="kw">const</span> mesh      <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Mesh</span>(geometry, material);
scene.<span class="fn">add</span>(mesh);

<span class="cm">// 4. Tambahkan cahaya</span>
scene.<span class="fn">add</span>(<span class="kw">new</span> <span class="cls">THREE.AmbientLight</span>(<span class="num">0xffffff</span>, <span class="num">1.0</span>));

<span class="cm">// 5. Renderer + loop animasi</span>
<span class="kw">const</span> renderer <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.WebGLRenderer</span>({ canvas: cv });
<span class="kw">function</span> <span class="fn">animate</span>() {
  <span class="fn">requestAnimationFrame</span>(animate);   <span class="cm">// jadwalkan frame berikutnya</span>
  renderer.<span class="fn">render</span>(scene, camera);   <span class="cm">// GAMBAR ke canvas</span>
}
<span class="fn">animate</span>();</pre>
        </div>
      </section>

      <!-- ═══ 02 RENDERER ═══ -->
      <section id="s2">
        <div class="section-title">
          <div class="section-num green">02</div>
          <h2>Renderer & Canvas</h2>
        </div>

        <p>Renderer adalah "mesin" yang mengubah data 3D menjadi pixel yang terlihat di layar. Three.js menggunakan WebGL (teknologi GPU browser).</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — RENDERER</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="kw">const</span> renderer <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.WebGLRenderer</span>({
  canvas: document.<span class="fn">getElementById</span>(<span class="str">'cv'</span>),  <span class="cm">// elemen &lt;canvas&gt; HTML</span>
  antialias: <span class="kw">true</span>,                          <span class="cm">// garis halus (anti-aliasing)</span>
  powerPreference: <span class="str">'high-performance'</span>        <span class="cm">// minta GPU terbaik</span>
});

renderer.<span class="fn">setPixelRatio</span>(Math.<span class="fn">min</span>(devicePixelRatio, <span class="num">2</span>));
<span class="cm">// devicePixelRatio = resolusi layar (retina=2, normal=1)</span>
<span class="cm">// max 2 supaya tidak terlalu berat di layar 4K</span>

renderer.<span class="fn">setSize</span>(innerWidth, innerHeight);
<span class="cm">// sesuaikan ukuran canvas dengan ukuran jendela browser</span>

renderer.shadowMap.enabled <span class="op">=</span> <span class="kw">true</span>;
renderer.shadowMap.type    <span class="op">=</span> <span class="cls">THREE.PCFSoftShadowMap</span>;
<span class="cm">// aktifkan bayangan. PCFSoftShadowMap = bayangan lembut/blur</span>
<span class="cm">// alternatif: THREE.BasicShadowMap (kasar tapi cepat)</span>

renderer.toneMapping         <span class="op">=</span> <span class="cls">THREE.ReinhardToneMapping</span>;
renderer.toneMappingExposure <span class="op">=</span> <span class="num">1.1</span>;
<span class="cm">// toneMapping = cara mengkonversi warna HDR ke layar biasa</span>
<span class="cm">// exposure 1.0 = normal, > 1.0 = lebih terang</span>

<span class="cm">// WAJIB: update ukuran saat browser di-resize</span>
window.<span class="fn">addEventListener</span>(<span class="str">'resize'</span>, () => {
  renderer.<span class="fn">setSize</span>(innerWidth, innerHeight);
  camera.<span class="fn">updateProjectionMatrix</span>(); <span class="cm">// update kamera juga</span>
});</pre>
        </div>

        <table>
          <tr>
            <th>Property</th>
            <th>Nilai</th>
            <th>Efek</th>
          </tr>
          <tr>
            <td><code>antialias</code></td>
            <td><code>true / false</code></td>
            <td>Garis halus vs bergerigi. true lebih bagus tapi sedikit lebih lambat</td>
          </tr>
          <tr>
            <td><code>shadowMap.type</code></td>
            <td><code>PCFSoftShadowMap</code></td>
            <td>Bayangan paling halus. Opsi lain: BasicShadowMap (cepat), PCFShadowMap (medium)</td>
          </tr>
          <tr>
            <td><code>toneMapping</code></td>
            <td><code>ReinhardToneMapping</code></td>
            <td>Warna terlihat natural. Alternatif: ACESFilmicToneMapping (cinematic)</td>
          </tr>
          <tr>
            <td><code>toneMappingExposure</code></td>
            <td><code>1.1</code></td>
            <td>Kecerahan output. Kurangi untuk lebih gelap, tambah untuk lebih terang</td>
          </tr>
        </table>
      </section>

      <!-- ═══ 03 SCENE ═══ -->
      <section id="s3">
        <div class="section-title">
          <div class="section-num orange">03</div>
          <h2>Scene & Fog</h2>
        </div>

        <p>Scene adalah "dunia" tempat semua objek hidup. Semua yang ingin terlihat <strong>harus di-add ke scene</strong>.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — SCENE</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="kw">const</span> scene <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Scene</span>();

<span class="cm">// Warna latar belakang (hexadecimal)</span>
scene.background <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="num">0x060810</span>); <span class="cm">// biru sangat gelap</span>
<span class="cm">// 0x060810 = hex RGB. Coba: 0x000000 (hitam), 0xffffff (putih), 0x87ceeb (langit)</span>

<span class="cm">// Kabut eksponensial (makin jauh = makin kabut)</span>
scene.fog <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.FogExp2</span>(
  <span class="num">0x060810</span>,  <span class="cm">// warna kabut (biasanya sama dengan background)</span>
  <span class="num">0.0053</span>     <span class="cm">// kepadatan kabut. Lebih besar = lebih kabut</span>
               <span class="cm">// 0.001 = sedikit kabut, 0.05 = sangat kabut</span>
);

<span class="cm">// Alternatif: kabut linear (dari jarak near ke far)</span>
scene.fog <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Fog</span>(
  <span class="num">0x060810</span>,  <span class="cm">// warna</span>
  <span class="num">50</span>,        <span class="cm">// near: mulai kabut dari jarak 50</span>
  <span class="num">200</span>        <span class="cm">// far: sepenuhnya kabut di jarak 200</span>
);

<span class="cm">// Tambah objek ke scene</span>
scene.<span class="fn">add</span>(mesh);   <span class="cm">// tambahkan mesh/cahaya apa saja</span>
scene.<span class="fn">remove</span>(mesh); <span class="cm">// hapus dari scene</span></pre>
        </div>
      </section>

      <!-- ═══ 04 CAMERA ═══ -->
      <section id="s4">
        <div class="section-title">
          <div class="section-num purple">04</div>
          <h2>Camera — Sudut Pandang</h2>
        </div>

        <p>Di proyek SPM ini kita pakai <strong>OrthographicCamera</strong> yang menghasilkan tampilan isometrik (tidak ada perspektif — objek jauh tidak mengecil).</p>

        <div class="callout info">
          <span class="callout-icon">📐</span>
          <div><strong>Beda dengan PerspectiveCamera:</strong> Perspektif = objek jauh terlihat kecil (seperti mata manusia). Orthographic = semua objek sama besar meski jauh (seperti denah teknik/game isometrik).</div>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — ORTHOGRAPHIC CAMERA</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="kw">const</span> Z <span class="op">=</span> <span class="num">21</span>; <span class="cm">// "zoom" — kecilkan Z untuk zoom in, besarkan untuk zoom out</span>
<span class="kw">const</span> A <span class="op">=</span> innerWidth / innerHeight; <span class="cm">// aspect ratio layar</span>

<span class="kw">const</span> cam <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.OrthographicCamera</span>(
  <span class="op">-</span>Z <span class="op">*</span> A,   <span class="cm">// left   (batas kiri tampilan)</span>
   Z <span class="op">*</span> A,   <span class="cm">// right  (batas kanan)</span>
   Z,       <span class="cm">// top    (batas atas)</span>
  <span class="op">-</span>Z,       <span class="cm">// bottom (batas bawah)</span>
   <span class="num">0.1</span>,     <span class="cm">// near   (jarak pandang minimum)</span>
   <span class="num">500</span>      <span class="cm">// far    (jarak pandang maksimum)</span>
);

<span class="cm">// Posisi kamera di dunia 3D (x, y, z)</span>
cam.position.<span class="fn">set</span>(<span class="num">42</span>, <span class="num">34</span>, <span class="num">42</span>);
<span class="cm">// x=42: geser ke kanan</span>
<span class="cm">// y=34: ketinggian (lebih tinggi = lihat lebih dari atas)</span>
<span class="cm">// z=42: geser ke depan</span>
<span class="cm">// Coba ubah untuk rotasi kamera: misal set(0, 50, 0) = tampak atas penuh</span>

<span class="cm">// Arahkan kamera ke titik ini</span>
cam.<span class="fn">lookAt</span>(<span class="num">1</span>, <span class="num">0</span>, <span class="num">2</span>); <span class="cm">// pusat scene kita</span>

<span class="cm">// WAJIB setelah ubah parameter kamera</span>
cam.<span class="fn">updateProjectionMatrix</span>();

<span class="cm">// ── Tips Kustomisasi Sudut Pandang ──</span>
<span class="cm">// Isometrik klasik 45°:  position.set(50, 50, 50)</span>
<span class="cm">// Tampak depan:          position.set(0, 0, 50)</span>
<span class="cm">// Tampak samping:        position.set(50, 0, 0)</span>
<span class="cm">// Tampak atas (top-down): position.set(0, 50, 0)</span></pre>
        </div>
      </section>

      <!-- ═══ 05 GEOMETRY ═══ -->
      <section id="s5">
        <div class="section-title">
          <div class="section-num red">05</div>
          <h2>Geometry — Bentuk 3D</h2>
        </div>

        <p>Geometry adalah data titik-titik (vertices) yang membentuk suatu benda. Belum ada warna — hanya bentuk murni. Three.js menyediakan banyak bentuk siap pakai:</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — SEMUA GEOMETRY YANG DIPAKAI</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── BoxGeometry (Kotak / Kubus) ──</span>
<span class="cm">// Dipakai untuk: gedung, dinding, atap, jalan, fondasi</span>
<span class="kw">new</span> <span class="cls">THREE.BoxGeometry</span>(
  width,   <span class="cm">// lebar (sumbu X)</span>
  height,  <span class="cm">// tinggi (sumbu Y)</span>
  depth    <span class="cm">// kedalaman (sumbu Z)</span>
);
<span class="cm">// Contoh: BOX(4, 2, 3) = kotak 4 lebar, 2 tinggi, 3 dalam</span>

<span class="cm">// ── CylinderGeometry (Silinder) ──</span>
<span class="cm">// Dipakai untuk: tiang lampu, pipa</span>
<span class="kw">new</span> <span class="cls">THREE.CylinderGeometry</span>(
  radiusTop,     <span class="cm">// jari-jari atas</span>
  radiusBottom,  <span class="cm">// jari-jari bawah</span>
  height,        <span class="cm">// tinggi</span>
  segments       <span class="cm">// jumlah sisi (lebih banyak = lebih bulat, tapi lebih berat)</span>
);
<span class="cm">// CYL(0.1, 0.1, 3, 6) = silinder lurus tinggi 3 dengan 6 sisi (heksagonal)</span>
<span class="cm">// CYL(0.5, 0.1, 2, 8) = kerucut terpotong (atas kecil bawah besar)</span>

<span class="cm">// ── PlaneGeometry (Bidang Datar) ──</span>
<span class="cm">// Dipakai untuk: lantai, halo/glow di tanah</span>
<span class="kw">new</span> <span class="cls">THREE.PlaneGeometry</span>(width, height);
<span class="cm">// Default: tegak vertikal → harus dirotasi -90° agar horizontal</span>
<span class="cm">// mesh.rotation.x = -Math.PI / 2;  // wajib untuk lantai!</span>

<span class="cm">// ── SphereGeometry (Bola) ──</span>
<span class="cm">// Dipakai untuk: beacon/lampu status, lampu jalan</span>
<span class="kw">new</span> <span class="cls">THREE.SphereGeometry</span>(
  radius,          <span class="cm">// ukuran bola</span>
  widthSegments,   <span class="cm">// kehalusan horizontal (min 3, rekomendasi 8-16)</span>
  heightSegments   <span class="cm">// kehalusan vertikal</span>
);

<span class="cm">// ── TorusGeometry (Donat / Cincin) ──</span>
<span class="cm">// Dipakai untuk: cincin berputar di beacon</span>
<span class="kw">new</span> <span class="cls">THREE.TorusGeometry</span>(
  radius,          <span class="cm">// jari-jari keseluruhan donat</span>
  tube,            <span class="cm">// ketebalan tabung</span>
  radialSegments,  <span class="cm">// kehalusan tabung</span>
  tubularSegments  <span class="cm">// kehalusan lingkaran</span>
);

<span class="cm">// ── CircleGeometry (Lingkaran Datar) ──</span>
<span class="cm">// Dipakai untuk: halo/glow di lantai bawah beacon</span>
<span class="kw">new</span> <span class="cls">THREE.CircleGeometry</span>(radius, segments);
<span class="cm">// Juga harus dirotasi: rotation.x = -Math.PI / 2</span>

<span class="cm">// ── EdgesGeometry (Garis Tepi) ──</span>
<span class="cm">// Dipakai untuk: outline/border bangunan</span>
<span class="kw">const</span> box <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.BoxGeometry</span>(<span class="num">2</span>, <span class="num">2</span>, <span class="num">2</span>);
<span class="kw">const</span> edges <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.EdgesGeometry</span>(box); <span class="cm">// ambil garis tepinya saja</span>
<span class="kw">const</span> line  <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.LineSegments</span>(edges,
  <span class="kw">new</span> <span class="cls">THREE.LineBasicMaterial</span>({ color: <span class="num">0x00ff00</span>, opacity: <span class="num">0.5</span>, transparent: <span class="kw">true</span> })
);
scene.<span class="fn">add</span>(line);</pre>
        </div>

        <table>
          <tr>
            <th>Geometry</th>
            <th>Kegunaan di SPM</th>
            <th>Parameter Utama</th>
          </tr>
          <tr>
            <td><code>BoxGeometry</code></td>
            <td>Bangunan, fondasi, jalan, atap</td>
            <td>width, height, depth</td>
          </tr>
          <tr>
            <td><code>CylinderGeometry</code></td>
            <td>Tiang lampu, pipa kabel</td>
            <td>radiusTop, radiusBottom, height, segments</td>
          </tr>
          <tr>
            <td><code>PlaneGeometry</code></td>
            <td>Lantai, shadow plane</td>
            <td>width, height — perlu rotasi!</td>
          </tr>
          <tr>
            <td><code>SphereGeometry</code></td>
            <td>Bohlam lampu, beacon bola</td>
            <td>radius, widthSeg, heightSeg</td>
          </tr>
          <tr>
            <td><code>TorusGeometry</code></td>
            <td>Cincin beacon berputar</td>
            <td>radius, tube, radialSeg, tubularSeg</td>
          </tr>
          <tr>
            <td><code>CircleGeometry</code></td>
            <td>Halo glow di lantai</td>
            <td>radius, segments — perlu rotasi!</td>
          </tr>
          <tr>
            <td><code>EdgesGeometry</code></td>
            <td>Outline/border bangunan</td>
            <td>wraps geometry lain</td>
          </tr>
        </table>
      </section>

      <!-- ═══ 06 MATERIAL ═══ -->
      <section id="s6">
        <div class="section-title">
          <div class="section-num cyan">06</div>
          <h2>Material — Tampilan Permukaan</h2>
        </div>

        <p>Material menentukan <em>bagaimana</em> permukaan sebuah objek terlihat — warnanya, apakah mengkilap, transparan, atau memancarkan cahaya sendiri.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — JENIS MATERIAL</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── MeshLambertMaterial (Yang paling banyak dipakai di SPM) ──</span>
<span class="cm">// Merespons cahaya, efisien/cepat, cocok untuk tampilan non-metalik</span>
<span class="kw">new</span> <span class="cls">THREE.MeshLambertMaterial</span>({
  color:             <span class="num">0x1a3820</span>,   <span class="cm">// warna dasar (hex). Kena pengaruh cahaya</span>
  emissive:          <span class="num">0x0a2010</span>,   <span class="cm">// warna yang dipancarkan sendiri (tidak perlu cahaya)</span>
  emissiveIntensity: <span class="num">0.3</span>,        <span class="cm">// kekuatan emissive. 0=tidak, 1=penuh, bisa > 1</span>
  transparent:       <span class="kw">true</span>,       <span class="cm">// aktifkan transparansi</span>
  opacity:           <span class="num">0.7</span>,        <span class="cm">// 0=tidak terlihat, 1=penuh. Butuh transparent:true</span>
  side:    <span class="cls">THREE.DoubleSide</span>       <span class="cm">// render kedua sisi (default: FrontSide)</span>
});

<span class="cm">// ── Tips emissive ──</span>
<span class="cm">// emissive = warna yang "bersinar" sendiri tanpa butuh cahaya</span>
<span class="cm">// Digunakan untuk: efek semi-warna pada bangunan, LED, glow</span>
<span class="cm">// emissive SAMA dengan color → objek terlihat "glowing"</span>
<span class="cm">// emissive lebih gelap dari color → aksen halus</span>

<span class="cm">// Helper yang dipakai di SPM:</span>
<span class="kw">const</span> ml <span class="op">=</span> (hex, emissive=<span class="num">0</span>, intensity=<span class="num">0</span>) =>
  <span class="kw">new</span> <span class="cls">THREE.MeshLambertMaterial</span>({ color: hex, emissive, emissiveIntensity: intensity });

<span class="kw">const</span> mt <span class="op">=</span> (hex, opacity, emissive=<span class="num">0</span>, intensity=<span class="num">0</span>) =>
  <span class="kw">new</span> <span class="cls">THREE.MeshLambertMaterial</span>({
    color: hex, transparent: <span class="kw">true</span>, opacity,
    emissive, emissiveIntensity: intensity
  });

<span class="cm">// Contoh penggunaan:</span>
ml(<span class="num">0x1a3820</span>)           <span class="cm">// hijau gelap solid (tidak bercahaya)</span>
ml(<span class="num">0x1a3820</span>, <span class="num">0x0a2010</span>, <span class="num">0.3</span>) <span class="cm">// hijau gelap dengan sedikit emit hijau</span>
mt(<span class="num">0x3870c0</span>, <span class="num">0.55</span>, <span class="num">0x183058</span>, <span class="num">0.55</span>) <span class="cm">// kaca biru semi-transparan</span></pre>
        </div>

        <h3>Material Array — 1 Objek, 6 Warna Berbeda</h3>
        <p>BoxGeometry punya 6 sisi. Kita bisa beri material berbeda untuk setiap sisi:</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — ARRAY MATERIAL (SISI BERBEDA)</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// Urutan: [kanan, kiri, atas, bawah, depan, belakang]</span>
<span class="cm">//         [+X,   -X,   +Y,  -Y,    +Z,    -Z    ]</span>
<span class="kw">const</span> materials <span class="op">=</span> [
  ml(<span class="num">0x1a3820</span>, <span class="num">0x0a2010</span>, <span class="num">0.15</span>),  <span class="cm">// kanan: hijau gelap + hint emit</span>
  ml(<span class="num">0x0e1812</span>, <span class="num">0x040e08</span>, <span class="num">0.10</span>),  <span class="cm">// kiri:  lebih gelap (bayangan)</span>
  ml(<span class="num">0x223428</span>, <span class="num">0x082014</span>, <span class="num">0.25</span>),  <span class="cm">// atas:  lebih terang (kena matahari)</span>
  ml(<span class="num">0x050806</span>),                    <span class="cm">// bawah: hampir hitam (tidak kena cahaya)</span>
  ml(<span class="num">0x1e2e22</span>, <span class="num">0x061810</span>, <span class="num">0.18</span>),  <span class="cm">// depan: medium</span>
  ml(<span class="num">0x0e1812</span>),                    <span class="cm">// belakang: gelap</span>
];

<span class="cm">// Pakai array material di mesh:</span>
<span class="kw">const</span> mesh <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Mesh</span>(<span class="kw">new</span> <span class="cls">THREE.BoxGeometry</span>(<span class="num">4</span>,<span class="num">2</span>,<span class="num">3</span>), materials);

<span class="cm">// ── Ini yang membuat gedung tidak "full hijau solid" ──</span>
<span class="cm">// Sisi atas lebih terang → efek kena matahari</span>
<span class="cm">// Sisi samping medium    → normal</span>
<span class="cm">// Sisi bawah gelap       → bayangan</span>
<span class="cm">// emissive rendah        → ada "warna" tapi tidak neon</span></pre>
        </div>

        <div class="callout tip">
          <span class="callout-icon">🎨</span>
          <div><strong>Tips warna semi:</strong> Gunakan warna <code>color</code> sangat gelap (misal <code>0x1a3820</code>) dengan <code>emissive</code> sedikit lebih terang (<code>0x0a2010</code>) dan intensity rendah (0.1–0.3). Hasilnya: terlihat kehijauan tapi tidak terlalu mencolok.</div>
        </div>
      </section>

      <!-- ═══ 07 MESH ═══ -->
      <section id="s7">
        <div class="section-title">
          <div class="section-num yellow">07</div>
          <h2>Mesh — Objek 3D Lengkap</h2>
        </div>

        <p>Mesh adalah kombinasi Geometry + Material. Inilah objek 3D yang sebenarnya bisa terlihat dan berinteraksi.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — MESH</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// Membuat mesh</span>
<span class="kw">const</span> mesh <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Mesh</span>(geometry, material);

<span class="cm">// ── userData: simpan data custom ──</span>
<span class="cm">// Sangat penting untuk raycasting (deteksi klik)!</span>
mesh.userData.id   <span class="op">=</span> <span class="str">'cr1'</span>;      <span class="cm">// ID bangunan</span>
mesh.userData.type <span class="op">=</span> <span class="str">'control'</span>; <span class="cm">// tipe bangunan</span>
mesh.name          <span class="op">=</span> <span class="str">'cr1'</span>;      <span class="cm">// nama mesh (untuk debugging)</span>

<span class="cm">// ── Bayangan ──</span>
mesh.castShadow    <span class="op">=</span> <span class="kw">true</span>; <span class="cm">// objek ini membuang bayangan ke objek lain</span>
mesh.receiveShadow <span class="op">=</span> <span class="kw">true</span>; <span class="cm">// objek ini menerima bayangan dari objek lain</span>

<span class="cm">// ── Tambah ke scene ──</span>
scene.<span class="fn">add</span>(mesh);

<span class="cm">// ── Helper addm() yang dipakai di SPM ──</span>
<span class="kw">function</span> <span class="fn">addm</span>(geo, mat, x, y, z, rx=<span class="num">0</span>, ry=<span class="num">0</span>, rz=<span class="num">0</span>) {
  <span class="kw">const</span> me <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Mesh</span>(geo, mat);
  me.position.<span class="fn">set</span>(x, y, z);     <span class="cm">// posisi di dunia</span>
  me.rotation.<span class="fn">set</span>(rx, ry, rz);   <span class="cm">// rotasi (dalam radian!)</span>
  me.castShadow <span class="op">=</span> me.receiveShadow <span class="op">=</span> <span class="kw">true</span>;
  scene.<span class="fn">add</span>(me);
  <span class="kw">return</span> me;
}

<span class="cm">// Contoh pakai:</span>
<span class="fn">addm</span>(BOX(<span class="num">4</span>,<span class="num">1</span>,<span class="num">3</span>), ml(<span class="num">0x1a3820</span>), <span class="num">0</span>, <span class="num">0.5</span>, <span class="num">0</span>);
<span class="cm">// kotak 4×1×3, di posisi x=0, y=0.5, z=0</span>

<span class="cm">// Lantai (PlaneGeometry perlu rotasi)</span>
<span class="fn">addm</span>(<span class="kw">new</span> <span class="cls">THREE.PlaneGeometry</span>(<span class="num">10</span>, <span class="num">10</span>), ml(<span class="num">0x1a2030</span>), <span class="num">0</span>, <span class="num">0</span>, <span class="num">0</span>,
     <span class="op">-</span>Math.PI/<span class="num">2</span>  <span class="cm">// rx = -90° agar horizontal</span>
);</pre>
        </div>
      </section>

      <!-- ═══ 08 POSISI & ROTASI ═══ -->
      <section id="s8">
        <div class="section-title">
          <div class="section-num pink">08</div>
          <h2>Posisi, Rotasi & Skala</h2>
        </div>

        <div class="callout warn">
          <span class="callout-icon">⚠️</span>
          <div><strong>Rotasi dalam Radian, bukan derajat!</strong> <code>Math.PI = 180°</code>, <code>Math.PI/2 = 90°</code>, <code>Math.PI/4 = 45°</code>. Untuk konversi: <code>derajat * Math.PI / 180</code></div>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — POSISI, ROTASI, SKALA</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── POSISI ──</span>
mesh.position.<span class="fn">set</span>(x, y, z);       <span class="cm">// set sekaligus</span>
mesh.position.x <span class="op">=</span> <span class="num">5</span>;               <span class="cm">// set satu sumbu</span>
mesh.position.<span class="fn">copy</span>(otherMesh.position); <span class="cm">// copy dari mesh lain</span>

<span class="cm">// Sistem koordinat Three.js:</span>
<span class="cm">// X → kanan (+) / kiri (-)</span>
<span class="cm">// Y → atas (+) / bawah (-)</span>
<span class="cm">// Z → depan (+) / belakang (-) (dari sudut pandang default)</span>

<span class="cm">// ── ROTASI (dalam RADIAN) ──</span>
mesh.rotation.<span class="fn">set</span>(rx, ry, rz);      <span class="cm">// set sekaligus</span>
mesh.rotation.y <span class="op">=</span> Math.PI / <span class="num">2</span>;      <span class="cm">// putar 90° di sumbu Y</span>
mesh.rotation.x <span class="op">=</span> <span class="op">-</span>Math.PI / <span class="num">2</span>;    <span class="cm">// putar -90° di sumbu X (untuk lantai/plane)</span>

<span class="cm">// Konversi derajat ke radian:</span>
mesh.rotation.y <span class="op">=</span> <span class="num">45</span> <span class="op">*</span> Math.PI / <span class="num">180</span>; <span class="cm">// 45 derajat</span>

<span class="cm">// Rotasi dalam kode building() untuk pintu:</span>
<span class="cm">// face='front'  → ry = 0           (pintu menghadap depan)</span>
<span class="cm">// face='left'   → ry = Math.PI/2   (pintu menghadap kiri)</span>
<span class="cm">// face='right'  → ry = -Math.PI/2  (pintu menghadap kanan)</span>

<span class="cm">// ── SKALA ──</span>
mesh.scale.<span class="fn">set</span>(sx, sy, sz);    <span class="cm">// scale sekaligus</span>
mesh.scale.x <span class="op">=</span> <span class="num">2</span>;              <span class="cm">// dua kali lebar</span>
mesh.scale.<span class="fn">set</span>(<span class="num">1</span>, <span class="num">1.5</span>, <span class="num">1</span>);    <span class="cm">// 1.5 kali lebih tinggi</span>

<span class="cm">// Animasi scale (dipakai di beacon)</span>
<span class="kw">const</span> s <span class="op">=</span> <span class="num">1</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">2.5</span>) <span class="op">*</span> <span class="num">0.1</span>;
<span class="cm">// sin = gelombang -1 sampai 1, × 0.1 = pulsasi ±10%</span>
mesh.scale.<span class="fn">set</span>(s, s, s); <span class="cm">// scale seragam semua sumbu</span></pre>
        </div>
      </section>

      <!-- ═══ 09 LIGHTING ═══ -->
      <section id="s9">
        <div class="section-title">
          <div class="section-num">09</div>
          <h2>Jenis-jenis Cahaya (Light)</h2>
        </div>

        <p>Cahaya sangat penting — tanpa cahaya, objek MeshLambertMaterial akan terlihat hitam semua. Di SPM kita pakai 4 jenis cahaya.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — SEMUA JENIS CAHAYA</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── 1. AmbientLight ──</span>
<span class="cm">// Cahaya merata ke seluruh scene. Tidak punya arah. Tidak membuat bayangan.</span>
<span class="cm">// Fungsi: supaya sisi yang tidak kena cahaya tidak hitam pekat</span>
<span class="kw">const</span> ambient <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.AmbientLight</span>(
  <span class="num">0x18202e</span>,  <span class="cm">// warna: biru gelap (ambient SPM)</span>
  <span class="num">1.7</span>        <span class="cm">// intensitas. Lebih besar = lebih terang merata</span>
);
scene.<span class="fn">add</span>(ambient);

<span class="cm">// ── 2. DirectionalLight ──</span>
<span class="cm">// Cahaya berarah seperti matahari (sinar-sinar sejajar)</span>
<span class="cm">// Bisa membuat bayangan (castShadow)</span>
<span class="kw">const</span> sun <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.DirectionalLight</span>(
  <span class="num">0xfff8e8</span>,  <span class="cm">// warna: putih kekuningan (matahari sore)</span>
  <span class="num">2.9</span>        <span class="cm">// intensitas</span>
);
sun.position.<span class="fn">set</span>(<span class="num">28</span>, <span class="num">44</span>, <span class="num">22</span>); <span class="cm">// arah datang cahaya (dari posisi ini menuju 0,0,0)</span>
sun.castShadow <span class="op">=</span> <span class="kw">true</span>;
sun.shadow.mapSize.<span class="fn">set</span>(<span class="num">4096</span>, <span class="num">4096</span>); <span class="cm">// resolusi bayangan (2048=cukup, 4096=halus)</span>
<span class="cm">// Atur area bayangan yang dihitung:</span>
sun.shadow.camera.left <span class="op">=</span> sun.shadow.camera.bottom <span class="op">=</span> <span class="op">-</span><span class="num">44</span>;
sun.shadow.camera.right <span class="op">=</span> sun.shadow.camera.top <span class="op">=</span> <span class="num">44</span>;
sun.shadow.bias <span class="op">=</span> <span class="op">-</span><span class="num">0.0008</span>; <span class="cm">// koreksi shadow acne (artefak garis bayangan)</span>
scene.<span class="fn">add</span>(sun);

<span class="cm">// ── 3. PointLight ──</span>
<span class="cm">// Cahaya dari satu titik ke segala arah (seperti bohlam)</span>
<span class="cm">// Dipakai: cahaya berwarna di atas CR, lampu jalan</span>
<span class="kw">const</span> pt <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.PointLight</span>(
  <span class="num">0x1a8fff</span>,  <span class="cm">// warna: biru (CR light)</span>
  <span class="num">5.5</span>,       <span class="cm">// intensitas. Tinggi = terang, rendah = redup</span>
  <span class="num">16</span>         <span class="cm">// jarak: cahaya hanya mencapai radius 16 unit</span>
              <span class="cm">// 0 = tidak terbatas</span>
);
pt.position.<span class="fn">set</span>(<span class="num">9.5</span>, <span class="num">5</span>, <span class="op">-</span><span class="num">3</span>); <span class="cm">// posisi di atas CR1</span>
scene.<span class="fn">add</span>(pt);
<span class="cm">// Animasi intensitas (berdenyut):</span>
pt.intensity <span class="op">=</span> <span class="num">4.8</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">1.6</span>) <span class="op">*</span> <span class="num">0.7</span>;
<span class="cm">// intensitas berfluktuasi antara 4.1 dan 5.5</span>

<span class="cm">// ── 4. HemisphereLight ──</span>
<span class="cm">// Cahaya langit + cahaya tanah sekaligus</span>
<span class="cm">// Memberikan nuansa outdoor yang natural</span>
<span class="kw">const</span> hemi <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.HemisphereLight</span>(
  <span class="num">0x18283a</span>,  <span class="cm">// skyColor: warna dari atas (biru gelap = langit malam)</span>
  <span class="num">0x080e0a</span>,  <span class="cm">// groundColor: warna dari bawah (hampir hitam = tanah gelap)</span>
  <span class="num">0.55</span>       <span class="cm">// intensitas</span>
);
scene.<span class="fn">add</span>(hemi);</pre>
        </div>

        <table>
          <tr>
            <th>Light</th>
            <th>Analogi</th>
            <th>Bayangan?</th>
            <th>Kapan dipakai</th>
          </tr>
          <tr>
            <td><code>AmbientLight</code></td>
            <td>Cahaya ruangan difus</td>
            <td>❌ Tidak</td>
            <td>Selalu ada — fill light dasar</td>
          </tr>
          <tr>
            <td><code>DirectionalLight</code></td>
            <td>Matahari</td>
            <td>✅ Ya</td>
            <td>Cahaya utama + bayangan</td>
          </tr>
          <tr>
            <td><code>PointLight</code></td>
            <td>Bohlam/lampu meja</td>
            <td>⚡ Bisa</td>
            <td>Cahaya lokal berwarna</td>
          </tr>
          <tr>
            <td><code>HemisphereLight</code></td>
            <td>Langit cerah outdoor</td>
            <td>❌ Tidak</td>
            <td>Nuansa outdoor natural</td>
          </tr>
          <tr>
            <td><code>SpotLight</code></td>
            <td>Lampu sorot</td>
            <td>✅ Ya</td>
            <td>Efek dramatis, sorot objek</td>
          </tr>
        </table>
      </section>

      <!-- ═══ 10 WARNA ═══ -->
      <section id="s10">
        <div class="section-title">
          <div class="section-num green">10</div>
          <h2>Warna & THREE.Color</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — CARA PAKAI WARNA</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── Format warna di Three.js ──</span>

<span class="cm">// 1. Hexadecimal (paling umum)</span>
<span class="num">0xff0000</span>  <span class="cm">// merah</span>
<span class="num">0x00ff00</span>  <span class="cm">// hijau</span>
<span class="num">0x0000ff</span>  <span class="cm">// biru</span>
<span class="num">0x1a8fff</span>  <span class="cm">// biru medium (CR color)</span>
<span class="num">0x060810</span>  <span class="cm">// biru sangat gelap (background)</span>

<span class="cm">// 2. THREE.Color object</span>
<span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="num">0xff3030</span>)      <span class="cm">// dari hex</span>
<span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="str">'#ff3030'</span>)     <span class="cm">// dari CSS string</span>
<span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="str">'red'</span>)         <span class="cm">// nama warna CSS</span>
<span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="num">1.0</span>, <span class="num">0.2</span>, <span class="num">0.1</span>) <span class="cm">// RGB 0-1</span>

<span class="cm">// ── Manipulasi warna ──</span>
<span class="kw">const</span> col <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Color</span>(<span class="num">0x5a9860</span>); <span class="cm">// hijau terang</span>

<span class="cm">// Gelapkan: multiply scalar</span>
col.<span class="fn">multiplyScalar</span>(<span class="num">0.3</span>)  <span class="cm">// 30% lebih gelap</span>
col.<span class="fn">multiplyScalar</span>(<span class="num">0.1</span>)  <span class="cm">// 10% (sangat gelap)</span>

<span class="cm">// Ambil hex value (untuk material)</span>
col.<span class="fn">getHex</span>()            <span class="cm">// → number hex</span>

<span class="cm">// Contoh di building() untuk membuat warna atap lebih gelap:</span>
<span class="kw">const</span> roofColor <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Color</span>(accentColor).<span class="fn">multiplyScalar</span>(<span class="num">0.22</span>).<span class="fn">getHex</span>();
<span class="cm">// accentColor misal 0x1a8fff (biru) → roofColor = biru 22% = sangat gelap</span>

<span class="cm">// ── Ubah warna material setelah dibuat ──</span>
mesh.material.color.<span class="fn">set</span>(<span class="num">0x00ff00</span>);     <span class="cm">// ganti warna</span>
mesh.material.emissive.<span class="fn">set</span>(<span class="num">0x003300</span>);  <span class="cm">// ganti emissive</span>
mesh.material.emissiveIntensity <span class="op">=</span> <span class="num">0.5</span>; <span class="cm">// ganti intensitas</span>
mesh.material.needsUpdate <span class="op">=</span> <span class="kw">true</span>;      <span class="cm">// update (kadang diperlukan)</span></pre>
        </div>
      </section>

      <!-- ═══ 11 SHADOW ═══ -->
      <section id="s11">
        <div class="section-title">
          <div class="section-num orange">11</div>
          <h2>Bayangan (Shadow)</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — SHADOW SETUP</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// WAJIB 3 langkah untuk bayangan:</span>

<span class="cm">// LANGKAH 1: Aktifkan di renderer</span>
renderer.shadowMap.enabled <span class="op">=</span> <span class="kw">true</span>;
renderer.shadowMap.type    <span class="op">=</span> <span class="cls">THREE.PCFSoftShadowMap</span>; <span class="cm">// lembut</span>
<span class="cm">// Tipe lain: BasicShadowMap (cepat/kasar), PCFShadowMap (medium)</span>

<span class="cm">// LANGKAH 2: Aktifkan di cahaya</span>
sun.castShadow <span class="op">=</span> <span class="kw">true</span>;
sun.shadow.mapSize.width  <span class="op">=</span> <span class="num">4096</span>; <span class="cm">// resolusi (power of 2: 512,1024,2048,4096)</span>
sun.shadow.mapSize.height <span class="op">=</span> <span class="num">4096</span>;
sun.shadow.camera.left <span class="op">=</span> <span class="op">-</span><span class="num">44</span>;     <span class="cm">// area yang dihitung</span>
sun.shadow.camera.right <span class="op">=</span> <span class="num">44</span>;
sun.shadow.camera.top <span class="op">=</span> <span class="num">44</span>;
sun.shadow.camera.bottom <span class="op">=</span> <span class="op">-</span><span class="num">44</span>;
sun.shadow.bias <span class="op">=</span> <span class="op">-</span><span class="num">0.0008</span>; <span class="cm">// koreksi shadow acne. Perlu di-tune tergantung scene</span>

<span class="cm">// LANGKAH 3: Set di setiap mesh</span>
mesh.castShadow    <span class="op">=</span> <span class="kw">true</span>;  <span class="cm">// mesh ini membuang bayangan</span>
mesh.receiveShadow <span class="op">=</span> <span class="kw">true</span>;  <span class="cm">// mesh ini menerima bayangan</span>

<span class="cm">// Tips: lantai wajib receiveShadow = true</span>
<span class="cm">// Objek kecil tak penting: castShadow = false untuk performa</span></pre>
        </div>
      </section>

      <!-- ═══ 12 RAYCASTING ═══ -->
      <section id="s12">
        <div class="section-title">
          <div class="section-num purple">12</div>
          <h2>Raycasting — Deteksi Klik pada Objek 3D</h2>
        </div>

        <p>Raycasting adalah teknik mengirim "sinar tak terlihat" dari posisi mouse menembus scene 3D, lalu mengecek objek mana yang berpotongan dengan sinar tersebut.</p>

        <div class="diagram">
          <div class="dia-row">
            <div class="dia-box blue">Mouse<br>Position</div>
            <div class="dia-arrow">→</div>
            <div class="dia-box orange">Normalized<br>Coords (-1 to 1)</div>
            <div class="dia-arrow">→</div>
            <div class="dia-box green">Ray dari<br>Camera</div>
            <div class="dia-arrow">→</div>
            <div class="dia-box purple">Intersect<br>Objects</div>
          </div>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — RAYCASTING</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// Setup (buat sekali)</span>
<span class="kw">const</span> RC <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Raycaster</span>();
<span class="kw">const</span> MV <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Vector2</span>(); <span class="cm">// posisi mouse normalized</span>

<span class="cm">// BODIES = array semua mesh yang bisa diklik</span>
<span class="cm">// Hanya tambahkan mesh yang memang perlu diklik (untuk performa)</span>
<span class="kw">const</span> BODIES <span class="op">=</span> []; <span class="cm">// diisi saat building() dibuat</span>

<span class="cm">// Event mousemove: hover effect</span>
canvas.<span class="fn">addEventListener</span>(<span class="str">'mousemove'</span>, e => {
  <span class="cm">// Konversi pixel → koordinat normalized (-1 sampai +1)</span>
  MV.x <span class="op">=</span> (e.clientX / innerWidth)  <span class="op">*</span> <span class="num">2</span> <span class="op">-</span> <span class="num">1</span>;
  MV.y <span class="op">=</span> <span class="op">-</span>((e.clientY / innerHeight) <span class="op">*</span> <span class="num">2</span> <span class="op">-</span> <span class="num">1</span>);
  <span class="cm">// Y dibalik (-) karena koordinat layar terbalik dari Three.js</span>

  <span class="cm">// Set ray dari kamera ke arah mouse</span>
  RC.<span class="fn">setFromCamera</span>(MV, cam);

  <span class="cm">// Cek persimpangan dengan objek di BODIES</span>
  <span class="kw">const</span> hits <span class="op">=</span> RC.<span class="fn">intersectObjects</span>(BODIES, <span class="kw">false</span>);
  <span class="cm">// parameter 2: false = tidak rekursif (tidak cek children)</span>
  <span class="cm">// true = rekursif (untuk group/hierarchy objek)</span>

  <span class="kw">if</span> (hits.length) {
    <span class="kw">const</span> firstHit <span class="op">=</span> hits[<span class="num">0</span>];          <span class="cm">// objek terdekat kamera</span>
    <span class="kw">const</span> id <span class="op">=</span> firstHit.object.userData.id; <span class="cm">// ambil ID dari userData</span>
    <span class="cm">// firstHit.point → titik xyz perpotongan</span>
    <span class="cm">// firstHit.distance → jarak dari kamera</span>
    canvas.style.cursor <span class="op">=</span> <span class="str">'pointer'</span>;
  } <span class="kw">else</span> {
    canvas.style.cursor <span class="op">=</span> <span class="str">''</span>;
  }
});

<span class="cm">// Event click: buka panel</span>
canvas.<span class="fn">addEventListener</span>(<span class="str">'click'</span>, e => {
  MV.x <span class="op">=</span> (e.clientX / innerWidth)  <span class="op">*</span> <span class="num">2</span> <span class="op">-</span> <span class="num">1</span>;
  MV.y <span class="op">=</span> <span class="op">-</span>((e.clientY / innerHeight) <span class="op">*</span> <span class="num">2</span> <span class="op">-</span> <span class="num">1</span>);
  RC.<span class="fn">setFromCamera</span>(MV, cam);
  <span class="kw">const</span> hits <span class="op">=</span> RC.<span class="fn">intersectObjects</span>(BODIES, <span class="kw">false</span>);
  <span class="kw">if</span> (hits.length) {
    <span class="fn">openPanel</span>(hits[<span class="num">0</span>].object.userData.id);
  }
});</pre>
        </div>
      </section>

      <!-- ═══ 13 ANIMATION LOOP ═══ -->
      <section id="s13">
        <div class="section-title">
          <div class="section-num red">13</div>
          <h2>Animation Loop — Jantung Three.js</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — ANIMATION LOOP</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="kw">let</span> T <span class="op">=</span> <span class="num">0</span>; <span class="cm">// waktu total (detik)</span>

<span class="kw">function</span> <span class="fn">animate</span>() {
  <span class="cm">// requestAnimationFrame: minta browser panggil animate() sebelum frame berikutnya</span>
  <span class="cm">// Otomatis ~60fps. Lebih efisien dari setInterval karena:</span>
  <span class="cm">// - pause saat tab tidak aktif (hemat baterai)</span>
  <span class="cm">// - sinkron dengan refresh rate monitor</span>
  <span class="fn">requestAnimationFrame</span>(animate);

  T <span class="op">+=</span> <span class="num">0.016</span>; <span class="cm">// ≈ 1/60 detik per frame. Dipakai untuk animasi sin/cos</span>

  <span class="cm">// ── Animasi menggunakan Math.sin ──</span>
  <span class="cm">// sin(T) = gelombang yang berulang dari -1 ke +1</span>

  <span class="cm">// Pulsasi scale beacon:</span>
  <span class="kw">const</span> s <span class="op">=</span> <span class="num">1</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">2.5</span>) <span class="op">*</span> <span class="num">0.1</span>;
  <span class="cm">// T*2.5 = kecepatan animasi (lebih besar = lebih cepat)</span>
  <span class="cm">// *0.1 = amplitudo (scale antara 0.9 dan 1.1)</span>
  beacon.scale.<span class="fn">set</span>(s, s, s);

  <span class="cm">// Pulsasi cahaya dengan fase berbeda tiap CR:</span>
  ptCr1.intensity <span class="op">=</span> <span class="num">4.8</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">1.6</span>)         <span class="op">*</span> <span class="num">0.7</span>; <span class="cm">// fase 0</span>
  ptCr2.intensity <span class="op">=</span> <span class="num">4.8</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">1.6</span> <span class="op">+</span> <span class="num">1.1</span>)    <span class="op">*</span> <span class="num">0.7</span>; <span class="cm">// fase +1.1 rad</span>
  ptCr3.intensity <span class="op">=</span> <span class="num">4.8</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T <span class="op">*</span> <span class="num">1.6</span> <span class="op">+</span> <span class="num">2.2</span>)    <span class="op">*</span> <span class="num">0.7</span>; <span class="cm">// fase +2.2 rad</span>
  <span class="cm">// +offset fase = tidak berkedip bersamaan</span>

  <span class="cm">// ── Rotasi objek ──</span>
  mesh.rotation.y <span class="op">+=</span> <span class="num">0.01</span>; <span class="cm">// rotasi terus-menerus</span>
  <span class="cm">// += berarti tambah setiap frame (~0.01 × 60fps = 0.6 rad/detik)</span>

  <span class="cm">// ── Highlight mesh saat hover/selected ──</span>
  BODIES.<span class="fn">forEach</span>(b => {
    <span class="kw">const</span> isHovered  <span class="op">=</span> hov <span class="op">===</span> b.userData.id;
    <span class="kw">const</span> isSelected <span class="op">=</span> curId <span class="op">===</span> b.userData.id;
    <span class="kw">const</span> mats <span class="op">=</span> Array.isArray(b.material) ? b.material : [b.material];
    mats.<span class="fn">forEach</span>(m => {
      <span class="kw">if</span> (!m.emissive) <span class="kw">return</span>;
      <span class="kw">if</span> (m._base <span class="op">===</span> undefined) m._base <span class="op">=</span> m.emissiveIntensity; <span class="cm">// simpan nilai awal</span>
      <span class="kw">if</span>      (isSelected) m.emissiveIntensity <span class="op">=</span> m._base <span class="op">+</span> <span class="num">0.28</span> <span class="op">+</span> Math.<span class="fn">sin</span>(T<span class="op">*</span><span class="num">3</span>)<span class="op">*</span><span class="num">.1</span>;
      <span class="kw">else if</span> (isHovered)  m.emissiveIntensity <span class="op">=</span> m._base <span class="op">+</span> <span class="num">0.18</span>;
      <span class="kw">else</span>                 m.emissiveIntensity <span class="op">=</span> m._base;
    });
  });

  <span class="cm">// WAJIB: render scene ke canvas di setiap frame</span>
  renderer.<span class="fn">render</span>(scene, cam);
}
<span class="fn">animate</span>(); <span class="cm">// mulai loop</span></pre>
        </div>
      </section>

      <!-- ═══ 14 LABEL ═══ -->
      <section id="s14">
        <div class="section-title">
          <div class="section-num cyan">14</div>
          <h2>Label 3D → 2D (Proyeksi ke Layar)</h2>
        </div>

        <p>Label nama bangunan adalah elemen HTML biasa (<code>&lt;div&gt;</code>) yang diposisikan sesuai proyeksi titik 3D ke layar. Diupdate setiap frame.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — PROYEKSI LABEL 3D KE 2D</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// Data label</span>
<span class="kw">const</span> LBLS <span class="op">=</span> [
  {
    id: <span class="str">'cr1'</span>,
    p:  <span class="kw">new</span> <span class="cls">THREE.Vector3</span>(<span class="num">9.5</span>, <span class="num">4.8</span>, <span class="op">-</span><span class="num">3</span>), <span class="cm">// posisi 3D di dunia</span>
    t:  [<span class="str">'CR 1'</span>, <span class="str">'CONTROL ROOM 1'</span>],   <span class="cm">// teks label</span>
    col:<span class="str">'#1a8fff'</span>
  }
];

<span class="cm">// Buat elemen HTML untuk setiap label</span>
LBLS.<span class="fn">forEach</span>(lb => {
  <span class="kw">const</span> div <span class="op">=</span> document.<span class="fn">createElement</span>(<span class="str">'div'</span>);
  div.style.position <span class="op">=</span> <span class="str">'absolute'</span>;
  div.style.pointerEvents <span class="op">=</span> lb.id ? <span class="str">'auto'</span> : <span class="str">'none'</span>;
  document.<span class="fn">getElementById</span>(<span class="str">'lyr'</span>).<span class="fn">appendChild</span>(div);
  lb.el <span class="op">=</span> div; <span class="cm">// simpan referensi elemen</span>
});

<span class="cm">// Di dalam animate(), update posisi setiap frame:</span>
<span class="kw">const</span> TMP <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.Vector3</span>(); <span class="cm">// reuse vector untuk efisiensi</span>

LBLS.<span class="fn">forEach</span>(lb => {
  <span class="cm">// Vector3.project(camera) → konversi 3D world ke normalized screen (-1 to 1)</span>
  TMP.<span class="fn">copy</span>(lb.p).<span class="fn">project</span>(cam);

  <span class="cm">// Konversi normalized → pixel layar</span>
  lb.el.style.left <span class="op">=</span> ((TMP.x <span class="op">*</span> <span class="num">0.5</span> <span class="op">+</span> <span class="num">0.5</span>) <span class="op">*</span> innerWidth)  <span class="op">+</span> <span class="str">'px'</span>;
  lb.el.style.top  <span class="op">=</span> ((<span class="op">-</span><span class="num">0.5</span> <span class="op">*</span> TMP.y <span class="op">+</span> <span class="num">0.5</span>) <span class="op">*</span> innerHeight) <span class="op">+</span> <span class="str">'px'</span>;
  <span class="cm">// Y dibalik karena layar Y turun, Three.js Y naik</span>

  <span class="cm">// Sembunyikan jika di belakang kamera (TMP.z >= 1)</span>
  lb.el.style.opacity <span class="op">=</span> TMP.z <span class="op">&lt;</span> <span class="num">1</span> ? <span class="str">'1'</span> : <span class="str">'0'</span>;
});</pre>
        </div>
      </section>

      <!-- ═══ 15 BUILDING() ═══ -->
      <section id="s15">
        <div class="section-title">
          <div class="section-num yellow">15</div>
          <h2>Memahami Fungsi building()</h2>
        </div>

        <p>Fungsi <code>building()</code> adalah wrapper yang membuat sebuah gedung lengkap dengan fondasi, dinding, atap, AC unit, dan dekorasi dalam satu panggilan.</p>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — PARAMETER building()</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="fn">building</span>(
  id,       <span class="cm">// string: identifier unik, misal 'cr1', 'cell1', 'pit3'</span>
            <span class="cm">// null jika tidak perlu klik</span>

  x, z,     <span class="cm">// posisi di dunia (x = kiri-kanan, z = depan-belakang)</span>
            <span class="cm">// Y tidak ditulis karena gedung selalu di atas tanah</span>

  w, d,     <span class="cm">// ukuran: w = lebar (X), d = kedalaman (Z)</span>

  h,        <span class="cm">// tinggi gedung</span>

  mats,     <span class="cm">// array 6 material [kanan,kiri,atas,bawah,depan,belakang]</span>
            <span class="cm">// atau single material</span>

  acol,     <span class="cm">// accent color (hex number): warna untuk outline, strip, atap</span>
            <span class="cm">// 0x1a8fff = biru, 0x5a9860 = hijau, 0xa83848 = merah</span>

  doorCfg,  <span class="cm">// konfigurasi pintu. null = tidak ada pintu</span>
            <span class="cm">// { face: 'front'/'left'/'right', off: offsetAlong }</span>
            <span class="cm">// Array = beberapa pintu</span>

  winCfg,   <span class="cm">// konfigurasi jendela. null = tidak ada</span>
            <span class="cm">// { face: 'front'/'left'/'right', count: jumlahJendela }</span>

  opts      <span class="cm">// opsi tambahan:</span>
            <span class="cm">// hv: jumlah AC unit di atap (0-3)</span>
            <span class="cm">// grid: true = tambah garis grid di atap</span>
            <span class="cm">// rk: jumlah rack server (untuk bangunan yang terlihat dari atas)</span>
);

<span class="cm">// ── Contoh nyata dari kode SPM ──</span>

<span class="cm">// Control Room (tinggi, ada jendela dan pintu)</span>
<span class="fn">building</span>(<span class="str">'cr1'</span>, <span class="num">9.5</span>, <span class="op">-</span><span class="num">3.0</span>, <span class="num">3.8</span>, <span class="num">3.0</span>, <span class="num">3.2</span>,
  <span class="fn">CRW_BLUE</span>(),          <span class="cm">// material biru</span>
  <span class="num">0x1a8fff</span>,            <span class="cm">// accent biru</span>
  { face: <span class="str">'left'</span>, off: <span class="num">0</span> },    <span class="cm">// 1 pintu di sisi kiri</span>
  { face: <span class="str">'front'</span>, count: <span class="num">2</span> }, <span class="cm">// 2 jendela di depan</span>
  { hv: <span class="num">2</span> }                      <span class="cm">// 2 AC unit di atap</span>
);

<span class="cm">// Test Cell (rendah, banyak equipment)</span>
<span class="fn">building</span>(<span class="str">'cell1'</span>, <span class="num">12</span>, <span class="num">8.5</span>, <span class="num">5.4</span>, <span class="num">4.8</span>, <span class="num">1.5</span>,
  <span class="fn">GW</span>(),               <span class="cm">// material hijau semi</span>
  <span class="num">0x4a8460</span>,           <span class="cm">// accent hijau medium</span>
  <span class="kw">null</span>,               <span class="cm">// tidak ada pintu</span>
  <span class="kw">null</span>,               <span class="cm">// tidak ada jendela</span>
  { hv: <span class="num">2</span>, grid: <span class="kw">true</span>, rk: <span class="num">4</span> } <span class="cm">// 2 AC, grid di atap, 4 rak</span>
);</pre>
        </div>
      </section>

      <!-- ═══ 16 UBAH WARNA ═══ -->
      <section id="s16">
        <div class="section-title">
          <div class="section-num pink">16</div>
          <h2>Cara Ubah Warna Gedung</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — KUSTOMISASI WARNA</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── Ganti warna Control Room ──</span>
<span class="cm">// Cari fungsi CRW_BLUE() dan edit warnanya:</span>
<span class="kw">const</span> <span class="fn">CRW_BLUE</span> <span class="op">=</span> () <span class="op">=></span> [
  ml(<span class="num">0x0a1828</span>, <span class="num">0x041020</span>, <span class="num">.35</span>),  <span class="cm">// kanan: [warna, emissive, intensitas]</span>
  ml(<span class="num">0x061018</span>, <span class="num">0x020814</span>, <span class="num">.25</span>),  <span class="cm">// kiri</span>
  ml(<span class="num">0x0e2038</span>, <span class="num">0x062030</span>, <span class="num">.55</span>),  <span class="cm">// atas (lebih terang)</span>
  ml(<span class="num">0x040810</span>),                   <span class="cm">// bawah (gelap)</span>
  ml(<span class="num">0x0c1e34</span>, <span class="num">0x051828</span>, <span class="num">.4</span> ),  <span class="cm">// depan</span>
  ml(<span class="num">0x061018</span>),                   <span class="cm">// belakang</span>
];

<span class="cm">// Untuk membuat CR HIJAU TEAL:</span>
<span class="kw">const</span> <span class="fn">CRW_TEAL</span> <span class="op">=</span> () <span class="op">=></span> [
  ml(<span class="num">0x0a2820</span>, <span class="num">0x041810</span>, <span class="num">.35</span>),
  ml(<span class="num">0x061810</span>, <span class="num">0x020e08</span>, <span class="num">.25</span>),
  ml(<span class="num">0x0e3828</span>, <span class="num">0x062018</span>, <span class="num">.55</span>), <span class="cm">// atas lebih terang</span>
  ml(<span class="num">0x041008</span>),
  ml(<span class="num">0x0c2e24</span>, <span class="num">0x051a14</span>, <span class="num">.4</span> ),
  ml(<span class="num">0x061810</span>),
];

<span class="cm">// Untuk membuat CR AMBER/KUNING:</span>
<span class="kw">const</span> <span class="fn">CRW_AMBER</span> <span class="op">=</span> () <span class="op">=></span> [
  ml(<span class="num">0x281800</span>, <span class="num">0x180e00</span>, <span class="num">.35</span>),
  ml(<span class="num">0x180e00</span>, <span class="num">0x0a0800</span>, <span class="num">.25</span>),
  ml(<span class="num">0x382200</span>, <span class="num">0x201400</span>, <span class="num">.55</span>),
  ml(<span class="num">0x100800</span>),
  ml(<span class="num">0x2e1e00</span>, <span class="num">0x181200</span>, <span class="num">.4</span> ),
  ml(<span class="num">0x180e00</span>),
];

<span class="cm">// PENTING: Selalu update accent color dan PointLight juga!</span>
<span class="fn">building</span>(<span class="str">'cr1'</span>, ..., <span class="fn">CRW_TEAL</span>(), <span class="num">0x00b090</span>, ...);
<span class="cm">//                             ↑ accentColor harus sesuai warna material</span>

<span class="kw">const</span> ptCr1 <span class="op">=</span> <span class="kw">new</span> <span class="cls">THREE.PointLight</span>(<span class="num">0x00b090</span>, <span class="num">5.5</span>, <span class="num">16</span>);
<span class="cm">//                                   ↑ warna cahaya sama</span>

<span class="kw">const</span> BCN <span class="op">=</span> { cr1: <span class="fn">mkBcn</span>(<span class="num">9.5</span>, <span class="op">-</span><span class="num">3</span>, <span class="num">0x00b090</span>) };
<span class="cm">//                                  ↑ warna beacon sama</span></pre>
        </div>

        <div class="callout tip">
          <span class="callout-icon">💡</span>
          <div><strong>Rumus warna semi yang bagus:</strong> Ambil warna target (misal biru <code>#1a8fff</code>), buat versi sangat gelap (~15% brightness) untuk <code>color</code>, versi 30-40% brightness untuk <code>emissive</code>, dengan <code>emissiveIntensity</code> 0.15–0.55. Sisi atas selalu lebih terang (kena matahari).</div>
        </div>
      </section>

      <!-- ═══ 17 TAMBAH BANGUNAN ═══ -->
      <section id="s17">
        <div class="section-title">
          <div class="section-num">17</div>
          <h2>Menambah Bangunan Baru</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — TAMBAH BANGUNAN BARU</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// LANGKAH 1: Tambah building() di scene</span>
<span class="fn">building</span>(
  <span class="str">'gudang1'</span>,  <span class="cm">// ID unik baru</span>
  <span class="op">-</span><span class="num">5</span>, <span class="num">15</span>,      <span class="cm">// posisi x=−5, z=15</span>
  <span class="num">6</span>, <span class="num">4</span>, <span class="num">2.5</span>,  <span class="cm">// lebar=6, dalam=4, tinggi=2.5</span>
  <span class="fn">GW</span>(),        <span class="cm">// material (GW=hijau, RW=merah, CRW_BLUE=biru)</span>
  <span class="num">0x4a8460</span>,   <span class="cm">// accent color</span>
  { face: <span class="str">'front'</span>, off: <span class="num">1</span> }, <span class="cm">// pintu di depan, geser kanan 1 unit</span>
  { face: <span class="str">'left'</span>, count: <span class="num">3</span> }, <span class="cm">// 3 jendela di sisi kiri</span>
  { hv: <span class="num">1</span>, grid: <span class="kw">true</span> }       <span class="cm">// 1 AC, ada grid di atap</span>
);

<span class="cm">// LANGKAH 2: Tambah label</span>
LBLS.<span class="fn">push</span>({
  id: <span class="str">'gudang1'</span>,
  p:  <span class="kw">new</span> <span class="cls">THREE.Vector3</span>(<span class="op">-</span><span class="num">5</span>, <span class="num">3.5</span>, <span class="num">15</span>), <span class="cm">// sedikit di atas bangunan</span>
  t:  <span class="str">'GUDANG 1'</span>,
  cls:<span class="str">'lbl-r'</span>
});

<span class="cm">// LANGKAH 3: Tambah data ke DATA object (untuk panel kanan)</span>
DATA.<span class="fn">gudang1</span> <span class="op">=</span> {
  tp: <span class="str">'GUDANG'</span>,
  nm: <span class="str">'Gudang 1'</span>,
  zn: <span class="str">'AREA PENYIMPANAN'</span>,
  col: <span class="str">'#4a8460'</span>,
  temp: <span class="num">28</span>, hum: <span class="num">65</span>, pwr: <span class="num">45</span>, volt: <span class="num">220</span>,
  st: <span class="str">'OPERATIONAL'</span>,
  locks: [...],
  events: [...],
  rooms: []
};

<span class="cm">// LANGKAH 4: Tambah elemen HTML label (harus setelah LBLS di-push)</span>
<span class="cm">// Atau biarkan loop LBLS.forEach di bawah yang handle otomatis</span>
<span class="cm">// asalkan push dilakukan SEBELUM forEach dijalankan</span></pre>
        </div>

        <table>
          <tr>
            <th>Parameter</th>
            <th>Pengaruh</th>
            <th>Contoh</th>
          </tr>
          <tr>
            <td><code>x, z</code> lebih positif</td>
            <td>Geser ke kanan / belakang</td>
            <td><code>20, 10</code></td>
          </tr>
          <tr>
            <td><code>x, z</code> lebih negatif</td>
            <td>Geser ke kiri / depan</td>
            <td><code>-15, -8</code></td>
          </tr>
          <tr>
            <td><code>h</code> lebih besar</td>
            <td>Gedung lebih tinggi</td>
            <td><code>4.0</code> = gedung besar</td>
          </tr>
          <tr>
            <td><code>w, d</code> lebih besar</td>
            <td>Gedung lebih lebar/dalam</td>
            <td><code>8, 6</code> = besar</td>
          </tr>
          <tr>
            <td><code>opts.hv: 3</code></td>
            <td>Lebih banyak AC</td>
            <td>3 unit AC di atap</td>
          </tr>
          <tr>
            <td><code>opts.rk: 6</code></td>
            <td>Lebih banyak rak</td>
            <td>6 rak server terlihat</td>
          </tr>
        </table>
      </section>

      <!-- ═══ 18 JALAN ═══ -->
      <section id="s18">
        <div class="section-title">
          <div class="section-num green">18</div>
          <h2>Membuat & Modifikasi Jalan</h2>
        </div>

        <div class="code-wrap">
          <div class="code-header"><span class="code-lang">JAVASCRIPT — FUNGSI JALAN</span><button class="copy-btn" onclick="copyCode(this)">copy</button></div>
          <pre><span class="cm">// ── addRoad(x1, z1, x2, z2, width) ──</span>
<span class="cm">// Buat jalan lurus dari titik A ke titik B</span>
<span class="cm">// Menggunakan trigonometri untuk menghitung sudut dan panjang</span>

<span class="kw">function</span> <span class="fn">addRoad</span>(x1, z1, x2, z2, width=<span class="num">1.0</span>) {
  <span class="kw">const</span> dx <span class="op">=</span> x2<span class="op">-</span>x1, dz <span class="op">=</span> z2<span class="op">-</span>z1;
  <span class="kw">const</span> len   <span class="op">=</span> Math.<span class="fn">sqrt</span>(dx<span class="op">*</span>dx <span class="op">+</span> dz<span class="op">*</span>dz); <span class="cm">// panjang (Pythagoras)</span>
  <span class="kw">const</span> angle <span class="op">=</span> Math.<span class="fn">atan2</span>(dx, dz);      <span class="cm">// sudut arah jalan (dalam radian)</span>
  <span class="kw">const</span> cx <span class="op">=</span> (x1<span class="op">+</span>x2)/<span class="num">2</span>, cz <span class="op">=</span> (z1<span class="op">+</span>z2)/<span class="num">2</span>; <span class="cm">// titik tengah</span>

  <span class="cm">// Badan jalan</span>
  <span class="fn">addm</span>(BOX(width, <span class="num">.06</span>, len), roadMat, cx, <span class="num">.04</span>, cz, <span class="num">0</span>, angle, <span class="num">0</span>);
  <span class="cm">// BOX: width=lebar, .06=tipis, len=panjang</span>
  <span class="cm">// Dirotasi sebesar 'angle' di sumbu Y agar menghadap arah yang benar</span>

  <span class="cm">// Marking garis kuning di tengah jalan</span>
  <span class="kw">for</span> (<span class="kw">let</span> i <span class="op">=</span> <span class="num">0</span>; i <span class="op">&lt;</span> Math.<span class="fn">floor</span>(len/<span class="num">1.2</span>); i++) {
    <span class="kw">const</span> t <span class="op">=</span> (i<span class="op">+</span><span class="num">.5</span>) / Math.<span class="fn">floor</span>(len/<span class="num">1.2</span>); <span class="cm">// posisi relatif (0-1)</span>
    <span class="kw">const</span> rx <span class="op">=</span> x1<span class="op">+</span>dx<span class="op">*</span>t, rz <span class="op">=</span> z1<span class="op">+</span>dz<span class="op">*</span>t;        <span class="cm">// posisi garis di sepanjang jalan</span>
    <span class="fn">addm</span>(BOX(<span class="num">.06</span>, <span class="num">.07</span>, <span class="num">.45</span>), roadLine, rx, <span class="num">.08</span>, rz, <span class="num">0</span>, angle, <span class="num">0</span>);
  }
}

<span class="cm">// ── addRoadCorner() ── membuat belokan jalan menggunakan arc ──</span>
<span class="kw">function</span> <span class="fn">addRoadCorner</span>(x, z, r, startAngle, endAngle, steps=<span class="num">8</span>, width=<span class="num">1.0</span>) {
  <span class="cm">// Bagi arc menjadi 'steps' segmen kecil</span>
  <span class="kw">const</span> da <span class="op">=</span> (endAngle <span class="op">-</span> startAngle) / steps;
  <span class="kw">for</span> (<span class="kw">let</span> i <span class="op">=</span> <span class="num">0</span>; i <span class="op">&lt;</span> steps; i++) {
    <span class="kw">const</span> a1 <span class="op">=</span> startAngle <span class="op">+</span> i<span class="op">*</span>da;
    <span class="kw">const</span> a2 <span class="op">=</span> a1 <span class="op">+</span> da;
    <span class="cm">// Hitung titik di lingkaran: x = cx + r*cos(angle)</span>
    <span class="fn">addRoad</span>(x<span class="op">+</span>r<span class="op">*</span>Math.<span class="fn">cos</span>(a1), z<span class="op">+</span>r<span class="op">*</span>Math.<span class="fn">sin</span>(a1),
            x<span class="op">+</span>r<span class="op">*</span>Math.<span class="fn">cos</span>(a2), z<span class="op">+</span>r<span class="op">*</span>Math.<span class="fn">sin</span>(a2), width);
  }
}

<span class="cm">// ── Contoh penggunaan untuk buat jalan baru ──</span>

<span class="cm">// Jalan lurus horizontal:</span>
<span class="fn">addRoad</span>(<span class="op">-</span><span class="num">20</span>, <span class="op">-</span><span class="num">12</span>,  <span class="num">20</span>, <span class="op">-</span><span class="num">12</span>,  <span class="num">1.2</span>); <span class="cm">// dari kiri ke kanan</span>

<span class="cm">// Jalan lurus vertikal:</span>
<span class="fn">addRoad</span>(<span class="op">-</span><span class="num">18</span>, <span class="op">-</span><span class="num">12</span>, <span class="op">-</span><span class="num">18</span>,  <span class="num">13</span>,  <span class="num">1.2</span>); <span class="cm">// dari atas ke bawah</span>

<span class="cm">// Jalan diagonal:</span>
<span class="fn">addRoad</span>(<span class="op">-</span><span class="num">10</span>, <span class="op">-</span><span class="num">5</span>,  <span class="num">5</span>,  <span class="num">8</span>,  <span class="num">1.0</span>); <span class="cm">// diagonal otomatis dihitung</span>

<span class="cm">// Belokan 90° kiri atas → kanan (searah jarum jam):</span>
<span class="fn">addRoadCorner</span>(
  <span class="op">-</span><span class="num">18</span>, <span class="op">-</span><span class="num">12</span>,  <span class="cm">// titik pusat belokan</span>
  <span class="num">1.2</span>,        <span class="cm">// radius belokan</span>
  Math.PI/<span class="num">2</span>,  <span class="cm">// startAngle = 90°</span>
  Math.PI,    <span class="cm">// endAngle = 180°</span>
  <span class="num">6</span>,          <span class="cm">// steps (lebih banyak = lebih halus)</span>
  <span class="num">1.2</span>         <span class="cm">// lebar jalan</span>
);

<span class="cm">// ── Ubah warna jalan ──</span>
<span class="kw">const</span> roadMat  <span class="op">=</span> ml(<span class="num">0x4a3800</span>, <span class="num">0xb08000</span>, <span class="num">.4</span>);  <span class="cm">// gelap + emit kuning</span>
<span class="kw">const</span> roadLine <span class="op">=</span> ml(<span class="num">0xffcc00</span>, <span class="num">0xffcc00</span>, <span class="num">1.2</span>); <span class="cm">// garis kuning terang</span>
<span class="cm">// Ganti 0xffcc00 dengan 0xffffff untuk garis putih</span>
<span class="cm">// Ganti 0x4a3800 dengan 0x182030 untuk aspal lebih gelap</span></pre>
        </div>

        <div class="callout warn">
          <span class="callout-icon">⚠️</span>
          <div><strong>Perhatikan koordinat Z:</strong> Di scene SPM, Z negatif = atas layar, Z positif = bawah layar. Jadi jalan dari atas ke bawah denah: <code>addRoad(x, -12, x, 13, 1.2)</code></div>
        </div>

        <table>
          <tr>
            <th>Sudut Belokan</th>
            <th>startAngle</th>
            <th>endAngle</th>
            <th>Posisi</th>
          </tr>
          <tr>
            <td>Kiri atas</td>
            <td><code>Math.PI/2</code></td>
            <td><code>Math.PI</code></td>
            <td>Pojok kiri atas area</td>
          </tr>
          <tr>
            <td>Kanan atas</td>
            <td><code>0</code></td>
            <td><code>Math.PI/2</code></td>
            <td>Pojok kanan atas</td>
          </tr>
          <tr>
            <td>Kiri bawah</td>
            <td><code>Math.PI</code></td>
            <td><code>Math.PI*1.5</code></td>
            <td>Pojok kiri bawah</td>
          </tr>
          <tr>
            <td>Kanan bawah</td>
            <td><code>Math.PI*1.5</code></td>
            <td><code>Math.PI*2</code></td>
            <td>Pojok kanan bawah</td>
          </tr>
        </table>
      </section>

      <div style="border-top:1px solid var(--border);padding-top:24px;margin-top:40px;color:var(--dim);font-size:12px;font-family:'IBM Plex Mono',monospace;">
        SPM Oil & Gas — Three.js Reference Guide · r128 · WebGL Isometric
      </div>

    </main>
  </div>

  <script>
    // Active nav highlight saat scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('nav a');
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 80) current = s.id;
      });
      navLinks.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
      });
    });

    // Copy button
    function copyCode(btn) {
      const pre = btn.closest('.code-wrap').querySelector('pre');
      const text = pre.innerText;
      navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'copied!';
        setTimeout(() => btn.textContent = 'copy', 1500);
      });
    }
  </script>
</body>

</html>