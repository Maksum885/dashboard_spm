@extends('guest.layout')

@section('title', 'Login')

@section('content')
<div class="login-shell">
  <div class="login-card">
    <div class="login-brand">
      <img src="{{ asset('images/logospm1.png') }}" alt="">
    </div>

    <h1 class="login-title">Login</h1>
    <p class="login-sub">Testing Bay</p>

    @if ($errors->any())
      <div class="login-alert" role="alert">
        <i class="ti ti-alert-circle" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form class="login-form" id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
      @csrf

      <div class="login-field">
        <label for="email">Email</label>
        <div class="login-input-wrap">
          <i class="ti ti-mail" aria-hidden="true"></i>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            autocomplete="email"
            required
            placeholder="email@example.com"
            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
          >
        </div>
        @error('email')
          <div class="login-field-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="login-field">
        <label for="password">Password</label>
        <div class="login-input-wrap login-input-wrap--pass">
          <i class="ti ti-lock" aria-hidden="true"></i>
          <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
            placeholder="Password"
            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
          >
          <button type="button" class="login-toggle" id="togglePass" aria-label="Show password">
            <i class="ti ti-eye" aria-hidden="true"></i>
          </button>
        </div>
        @error('password')
          <div class="login-field-error">{{ $message }}</div>
        @enderror
      </div>

      <label class="login-remember">
        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
        Remember me
      </label>

      <button type="submit" class="login-submit" id="btnLogin">
        <span class="spinner" aria-hidden="true"></span>
        <span class="login-submit-text">Login</span>
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var pass = document.getElementById('password');
  var btn = document.getElementById('togglePass');
  if (btn && pass) {
    btn.addEventListener('click', function () {
      var show = pass.type === 'password';
      pass.type = show ? 'text' : 'password';
      var icon = btn.querySelector('.ti');
      if (icon) {
        icon.className = show ? 'ti ti-eye-off' : 'ti ti-eye';
      }
      btn.setAttribute('aria-label', show ? 'Hide Password' : 'Show Password');
    });
  }
  var form = document.getElementById('loginForm');
  var submit = document.getElementById('btnLogin');
  var text = submit && submit.querySelector('.login-submit-text');
  if (form && submit) {
    form.addEventListener('submit', function () {
      submit.disabled = true;
      submit.classList.add('is-loading');
      if (text) text.textContent = 'Logging in...';
    });
  }
})();
</script>
@endpush
