<!DOCTYPE html>
<html lang="en" class="settings-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Camera settings</title>
@vite(['resources/css/dashboard.css', 'resources/js/profile-menu.js'])
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
  <div class="hdr-r dash-topbar-actions settings-app__hdr-actions">
    @include('partials.profile-menu')
  </div>
</header>

<main class="settings-layout">
  <div class="admin-page-head admin-page-head--stack">
    <div>
      <h1 class="settings-page-title">Camera settings</h1>
      <p class="settings-page-lead">Configure RTSP cameras per test room. The CV service reads enabled entries from the server.</p>
    </div>
    <div class="admin-page-head-actions">
      <a href="{{ route('dashboard') }}" class="admin-btn admin-btn--ghost">
        <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to dashboard
      </a>
    </div>
  </div>

  <section class="settings-section" aria-labelledby="camera-heading">
    <h2 id="camera-heading" class="settings-section-title"><i class="ti ti-video" aria-hidden="true"></i> RTSP cameras</h2>

    @if (session('status'))
      <div class="settings-flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($rooms->isEmpty())
      <p class="settings-empty">No test rooms found. Run <code>php artisan db:seed</code> first.</p>
    @else
      @foreach ([
        'pit' => ['label' => 'Test pits', 'icon' => 'ti-box-multiple'],
        'cell' => ['label' => 'Test cells', 'icon' => 'ti-building'],
        'other' => ['label' => 'Other rooms', 'icon' => 'ti-layout-grid'],
      ] as $kind => $meta)
        @php $group = $roomsByKind[$kind] ?? collect(); @endphp
        @if ($group->isNotEmpty())
          <h3 class="settings-group-title" id="settings-cam-group-{{ $kind }}">
            <i class="ti {{ $meta['icon'] }}" aria-hidden="true"></i> {{ $meta['label'] }}
            <span class="settings-group-count">{{ $group->count() }}</span>
          </h3>
          <div class="settings-cards-grid">
            @foreach ($group as $room)
              @include('operator.partials.camera-room-card', ['room' => $room, 'highlightRoomId' => $highlightRoomId])
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
