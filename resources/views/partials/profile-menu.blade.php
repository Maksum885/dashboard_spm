@auth
<div class="dash-profile-wrap" id="dash-profile-wrap">
  <button type="button" class="dash-profile-btn" id="dash-profile-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="dash-profile-dd">
    @php
      $parts = preg_split('/\s+/', trim(auth()->user()->name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
      $initials = '';
      foreach (array_slice($parts, 0, 2) as $w) {
          $initials .= strtoupper(mb_substr($w, 0, 1));
      }
    @endphp
    <span class="dash-profile-avatar" aria-hidden="true">{{ $initials !== '' ? $initials : '?' }}</span>
    <span class="dash-profile-meta">
      <span class="dash-profile-name">{{ auth()->user()->name }}</span>
    </span>
    <i class="ti ti-chevron-down dash-profile-chevron" aria-hidden="true"></i>
  </button>
  <div class="dash-profile-dd" id="dash-profile-dd" role="menu" aria-labelledby="dash-profile-trigger">
    @if(auth()->user()->isAdmin())
      <div class="dash-profile-dd-head">Settings</div>
      <a href="{{ route('admin.operators.index') }}" class="dash-profile-dd-item" role="menuitem">
        <i class="ti ti-users" aria-hidden="true"></i>
        <span><strong>User accounts</strong><small>Users, roles, and rooms</small></span>
      </a>
      <a href="{{ route('settings.plc') }}" class="dash-profile-dd-item" role="menuitem">
        <i class="ti ti-plug-connected" aria-hidden="true"></i>
        <span><strong>PLC configuration</strong><small>IP, port, devices</small></span>
      </a>
      <a href="{{ route('admin.activity-logs.index') }}" class="dash-profile-dd-item" role="menuitem">
        <i class="ti ti-history" aria-hidden="true"></i>
        <span><strong>PLC register log</strong><small>Value change history</small></span>
      </a>
    @elseif(auth()->user()->role !== 'viewer')
      <div class="dash-profile-dd-head">Settings</div>
      <a href="{{ route('settings.plc') }}" class="dash-profile-dd-item" role="menuitem">
        <i class="ti ti-plug-connected" aria-hidden="true"></i>
        <span><strong>PLC configuration</strong><small>IP, port, devices</small></span>
      </a>
    @endif
    <a href="{{ route('account.password.edit') }}" class="dash-profile-dd-item" role="menuitem">
      <i class="ti ti-key" aria-hidden="true"></i>
      <span><strong>Change password</strong><small>Update your login password</small></span>
    </a>
    <div class="dash-profile-dd-sep" role="separator"></div>
    <form method="POST" action="{{ route('logout') }}" class="dash-profile-dd-form" onsubmit="sessionStorage.removeItem('spm_auth_token')">
      @csrf
      <button type="submit" class="dash-profile-dd-item dash-profile-dd-item--danger" role="menuitem">
        <i class="ti ti-logout-2" aria-hidden="true"></i>
        <span><strong>Sign out</strong><small>End session</small></span>
      </button>
    </form>
  </div>
</div>
@endauth
