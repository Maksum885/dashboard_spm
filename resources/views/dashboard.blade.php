<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPM Oil & Gas</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css', 'resources/js/app.js'])
</head>
<body>
<canvas id="cv"></canvas>

<header class="hdr">
  <div class="logo">
    <img src="{{ asset('images/logospm1.png') }}" alt="SPM" class="logo-img">
    <span class="logo-fallback">SPM OIL &amp; GAS</span>
  </div>
  <div class="hdr-r">
    <div class="clk" id="clk">00:00:00</div>
    <button type="button" class="ham" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
  </div>
</header>

<aside class="lsb" id="lsb">
  <div class="lscroll">
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-building"></i>CONTROL ROOMS</div>
      </div>

      <div class="blk-body-flush">
        <div class="cr-item active cr-item--ic" id="nav-cr1">
          <div class="cr-info" onclick="openPanel('cr1')">
            <div class="cr-name">Control Room 1</div>
            <div class="cr-sub">Cell 1 · Cell 2 · Cell 3</div>
          </div>
          <button type="button" class="cr-expand-btn" id="exp-cr1" onclick="toggleRooms('cr1')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr1"></div>
        <div class="cr-item cr-item--ic" id="nav-cr2">
          <div class="cr-info" onclick="openPanel('cr2')">
            <div class="cr-name">Control Room 2</div>
            <div class="cr-sub">Cell 4 · Cell 5 · Pit 1 · Pit 2</div>
          </div>
          <button type="button" class="cr-expand-btn" id="exp-cr2" onclick="toggleRooms('cr2')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr2"></div>
        <div class="cr-item cr-item--ic" id="nav-cr3">
          <div class="cr-info" onclick="openPanel('cr3')">
            <div class="cr-name">Control Room 3</div>
            <div class="cr-sub">Pit 3 · Pit 4 · Pit 5 · Pit 6</div>
          </div>
          <button type="button" class="cr-expand-btn" id="exp-cr3" onclick="toggleRooms('cr3')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr3"></div>
      </div>
    </div>
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-bell-ringing"></i>ALARM AKTIF</div>
        <span class="bc cr" id="al-count">0</span>
      </div>
      <div class="bb al-legend-wrap">
        <div class="al-subsection">
          <div class="al-heading">Alarm</div>
          <div id="al-list">
            <div class="al-empty-msg">Tidak ada alarm aktif</div>
          </div>
        </div>
        <div class="al-subsection">
          <div class="al-heading">Area Alerts</div>
          <div id="area-list">
            <div class="al-empty-msg">Tidak ada area alert</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</aside>

<svg id="lyr-svg"></svg>
<div class="lyr" id="lyr"></div>
<div id="tip"></div>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<div class="rpanel" id="rpanel">
  <div class="camera-view" id="camera-view">
    <video id="camera" autoplay playsinline></video>
  </div>
  <div class="rp-accent" id="rp-accent"></div>
  <div class="rp-hdr">
    <div class="rp-hdr-row">
      <div>
        <div class="rp-breadcrumb is-hidden" id="rp-breadcrumb"></div>
        <div class="rp-type" id="rp-type">—</div>
        <div class="rp-name" id="rp-name">—</div>
        <div class="rp-zone" id="rp-zone">—</div>
      </div>
      <button type="button" class="rp-close" onclick="closePanel()"><i class="ti ti-x"></i></button>
    </div>
  </div>
  <div class="rp-tabs" id="rp-tabs">
    <div class="rp-tab on" id="tab-s" onclick="swTab('s')">TEKANAN</div>
    <div class="rp-tab" id="tab-l" onclick="swTab('l')">LOCK</div>
    <div class="rp-tab" id="tab-e" onclick="swTab('e')">LOG</div>
  </div>
  <div class="rp-body">
    <div id="pane-s"></div>
    <div id="pane-l"></div>
    <div id="pane-e"></div>
  </div>
</div>

<div class="toast" id="toast"></div>
<span id="hkpi-alarm" class="dashboard-kpi-hidden"></span>
<span id="hkpi-alarm-dot" class="dashboard-kpi-hidden"></span>
<span id="hkpi-active" class="dashboard-kpi-hidden"></span>
<span id="hkpi-pwr" class="dashboard-kpi-hidden"></span>
</body>
</html>
