@php
  $rid = $d->testing_room_id;
  $hl = $highlightRoomId !== null && (int) $highlightRoomId === (int) $rid;
  $st = $d->status ?? 'offline';
@endphp
<article class="settings-card {{ $hl ? 'settings-card--highlight' : '' }}" id="room-{{ $rid }}">
  <header class="settings-card-head">
    <div>
      <h3 class="settings-card-room">{{ $d->testingRoom->name ?? 'Room' }}</h3>
      <p class="settings-card-seen">
        Last seen: <strong>{{ $d->last_seen_at ? $d->last_seen_at->format('d M Y, H:i') : 'Never' }}</strong>
      </p>
    </div>
    <div class="settings-card-status">
      @if ($st === 'online')
        <span class="settings-badge settings-badge--ok">Online</span>
      @elseif ($st === 'offline')
        <span class="settings-badge settings-badge--off">Offline</span>
      @else
        <span class="settings-badge settings-badge--unk">{{ $st }}</span>
      @endif
    </div>
  </header>

  <form method="POST" action="{{ route('settings.plc.update', $d) }}" class="settings-form">
    @csrf
    @method('PUT')
    <div class="settings-form-grid settings-form-grid--card">
      <div class="settings-field settings-field--wide">
        <label for="ip-{{ $d->id }}">PLC IP address</label>
        <input id="ip-{{ $d->id }}" type="text" name="ip_address" value="{{ old('ip_address.'.$d->id, $d->ip_address) }}" required placeholder="192.168.1.10" inputmode="numeric" autocomplete="off">
      </div>
      <div class="settings-field">
        <label for="port-{{ $d->id }}">Port</label>
        <input id="port-{{ $d->id }}" type="number" name="port" value="{{ old('port.'.$d->id, $d->port) }}" min="1" max="65535" required>
      </div>
      <div class="settings-field">
        <label for="uid-{{ $d->id }}">Unit ID</label>
        <input id="uid-{{ $d->id }}" type="number" name="unit_id" value="{{ old('unit_id.'.$d->id, $d->unit_id) }}" min="0" max="255" required title="Modbus slave / unit ID">
      </div>
    </div>
    <div class="settings-field-check">
      <input type="hidden" name="is_enabled" value="0">
      <input id="en-{{ $d->id }}" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled.'.$d->id, $d->is_enabled))>
      <label for="en-{{ $d->id }}">Enable polling for this room</label>
    </div>
    <button type="submit" class="settings-save-btn">Save</button>
  </form>
</article>
