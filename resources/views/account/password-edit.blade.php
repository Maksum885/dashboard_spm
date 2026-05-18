<!DOCTYPE html>
<html lang="id" class="settings-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ubah kata sandi — SPM SCADA</title>
@vite(['resources/css/dashboard.css', 'resources/js/profile-menu.js'])
@if(session('api_token'))
<script>
  sessionStorage.setItem('spm_auth_token', @json(session('api_token')));
</script>
@endif
</head>
<body class="settings-app">
<header class="dash-topbar hdr settings-app__hdr">
  <a href="{{ route('dashboard') }}" class="dash-brand logo settings-app__brand" title="Kembali ke dashboard">
    <img src="{{ asset('images/logospm1.png') }}" alt="SPM Oil &amp; Gas" class="logo-img">
  </a>
  <div class="hdr-r dash-topbar-actions settings-app__hdr-actions">
    <a href="{{ route('dashboard') }}" class="hdr-btn" title="Dashboard"><i class="ti ti-layout-dashboard"></i><span class="hdr-btn-lbl">Dashboard</span></a>
    @include('partials.profile-menu')
  </div>
</header>

<main class="settings-layout">
  <h1 class="settings-page-title">Ubah kata sandi</h1>
  <p class="settings-page-lead">Masukkan kata sandi saat ini, lalu kata sandi baru. Semua peran (operator, viewer, admin) dapat mengganti password sendiri di sini.</p>

  @if (session('status'))
    <div class="settings-flash" role="status">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="settings-flash settings-flash--danger" role="alert">
      <ul class="settings-error-list">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="settings-section" aria-labelledby="pw-heading">
    <h2 id="pw-heading" class="settings-section-title"><i class="ti ti-key" aria-hidden="true"></i> Akun: {{ $user->name }}</h2>

    <article class="settings-card" style="max-width: 520px">
      <form method="POST" action="{{ route('account.password.update') }}" class="settings-form">
        @csrf
        @method('PUT')
        <div class="settings-form-grid" style="grid-template-columns: 1fr">
          <div class="settings-field settings-field--wide">
            <label for="current_password">Kata sandi saat ini</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password" value="">
          </div>
          <div class="settings-field settings-field--wide">
            <label for="password">Kata sandi baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8">
          </div>
          <div class="settings-field settings-field--wide">
            <label for="password_confirmation">Ulangi kata sandi baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8">
          </div>
        </div>
        <button type="submit" class="settings-save-btn">Simpan kata sandi</button>
      </form>
    </article>
  </section>
</main>
</body>
</html>
