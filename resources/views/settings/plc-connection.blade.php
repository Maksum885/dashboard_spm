<!DOCTYPE html>
<html lang="en" class="settings-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings — SPM SCADA</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css'])
@if(session('api_token'))
<script>
  sessionStorage.setItem('spm_auth_token', @json(session('api_token')));
</script>
@endif
</head>
<body class="settings-app">
<header class="dash-topbar hdr settings-app__hdr">
  <a href="{{ route('dashboard') }}" class="dash-brand logo settings-app__brand" title="Back to dashboard">
    <img src="{{ asset('images/logospm1.png') }}" alt="SPM Oil &amp; Gas" class="logo-img">
  </a>
  <div class="hdr-r dash-topbar-actions">
    <a href="{{ route('dashboard') }}" class="hdr-btn" title="Dashboard"><i class="ti ti-layout-dashboard"></i><span class="hdr-btn-lbl">Dashboard</span></a>
    <form method="POST" action="{{ route('logout') }}" class="hdr-logout-form" onsubmit="sessionStorage.removeItem('spm_auth_token')">
      @csrf
      <button type="submit" class="hdr-btn dash-btn-out" title="Logout"><i class="ti ti-logout-2"></i><span class="hdr-btn-lbl">Logout</span></button>
    </form>
  </div>
</header>

<main class="settings-layout">
  <h1 class="settings-page-title">Settings</h1>
  <p class="settings-page-lead">Manage how each test room talks to its PLC. Changes here are saved to the server only.</p>

  <section class="settings-section" aria-labelledby="plc-heading">
    <h2 id="plc-heading" class="settings-section-title"><i class="ti ti-plug-connected" aria-hidden="true"></i> PLC connection</h2>

    <div class="settings-help" role="note">
      <strong>How it works</strong>
      <ul class="settings-help-list">
        <li><strong>Ethernet cable alone</strong> does not connect this app to the PLC. You must <strong>Save</strong> the correct IP/port/unit here, then run the <strong>Python Modbus bridge</strong> on a computer that can ping the PLC. The bridge opens the TCP connection and sends data to Laravel.</li>
        <li>There is <strong>no separate “Connect” button</strong> in the browser: when the bridge polls successfully, the room shows <strong>Online</strong> and “Last seen” updates.</li>
        <li><strong>Device name</strong> is fixed to the room (Test Pit / Test Cell) so labels cannot be mistyped.</li>
      </ul>
    </div>

    @if (session('status'))
      <div class="settings-flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($devices->isEmpty())
      <p class="settings-empty">No PLC devices found. Run <code>php artisan db:seed</code> or add rooms in the database.</p>
    @else
      @foreach ([
        'pit' => ['label' => 'Test pits', 'icon' => 'ti-box-multiple'],
        'cell' => ['label' => 'Test cells', 'icon' => 'ti-building'],
        'other' => ['label' => 'Other rooms', 'icon' => 'ti-layout-grid'],
      ] as $kind => $meta)
        @php $group = $devicesByKind[$kind] ?? collect(); @endphp
        @if ($group->isNotEmpty())
          <h3 class="settings-group-title" id="settings-group-{{ $kind }}">
            <i class="ti {{ $meta['icon'] }}" aria-hidden="true"></i> {{ $meta['label'] }}
            <span class="settings-group-count">{{ $group->count() }}</span>
          </h3>
          <div class="settings-cards-grid">
            @foreach ($group as $d)
              @include('settings.partials.plc-device-card', ['d' => $d, 'highlightRoomId' => $highlightRoomId])
            @endforeach
          </div>
        @endif
      @endforeach
    @endif
  </section>
</main>
@if ($highlightRoomId)
<script>
  document.getElementById("room-{{ $highlightRoomId }}")?.scrollIntoView({ behavior: "smooth", block: "center" });
</script>
@endif
</body>
</html>
