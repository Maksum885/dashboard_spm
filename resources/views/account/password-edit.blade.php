<!DOCTYPE html>
<html lang="en" class="settings-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change password — SPM SCADA</title>
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

<main class="settings-layout settings-layout--account">
  <div class="admin-page admin-page--narrow">
    <div class="admin-page-head admin-page-head--stack">
      <div>
        <h1 class="admin-page-title">Change password</h1>
      </div>
      <div class="admin-page-head-actions">
        <a href="{{ route('dashboard') }}" class="admin-btn admin-btn--ghost">
          <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to dashboard
        </a>
      </div>
    </div>

    @if (session('status'))
      <div class="admin-flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="admin-flash admin-flash--err" role="alert">
        <ul class="admin-flash-list">
          @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <article class="admin-card account-password-card">
      <div class="account-password-card-head">
        <i class="ti ti-user-circle" aria-hidden="true"></i>
        <span>Account: <strong>{{ $user->name }}</strong></span>
      </div>

      <form method="POST" action="{{ route('account.password.update') }}" class="admin-form">
        @csrf
        @method('PUT')
        <div class="admin-form-grid account-password-grid">
          <div class="admin-field admin-field--full">
            <label for="current_password">Current password</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
          </div>
          <div class="admin-field admin-field--full">
            <label for="password">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8">
          </div>
          <div class="admin-field admin-field--full">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8">
          </div>
          <div class="admin-form-actions admin-field--full">
            <button type="submit" class="admin-btn admin-btn--primary">Save password</button>
          </div>
        </div>
      </form>
    </article>
  </div>
</main>
</body>
</html>
