@php
  $rid = $room->id;
  $hl = $highlightRoomId !== null && (int) $highlightRoomId === (int) $rid;
@endphp
<article class="settings-card {{ $hl ? 'settings-card--highlight' : '' }}" id="room-{{ $rid }}">
  <header class="settings-card-head">
    <div>
      <h3 class="settings-card-room">{{ $room->name }}</h3>
      <p class="settings-card-seen">Two camera slots per test room (RTSP)</p>
    </div>
  </header>

  @foreach ($room->roomCameras as $cam)
    <div class="settings-camera-slot">
      <h4 class="settings-camera-slot__title">{{ $cam->name }} <span class="settings-group-count">Slot {{ $cam->slot }}</span></h4>
      <form method="POST" action="{{ route('settings.cameras.update', $cam) }}" class="settings-form">
        @csrf
        @method('PUT')
        <div class="settings-form-grid settings-form-grid--card">
          <div class="settings-field settings-field--wide">
            <label for="host-{{ $cam->id }}">RTSP host</label>
            <input id="host-{{ $cam->id }}" type="text" name="rtsp_host" value="{{ old('rtsp_host.'.$cam->id, $cam->rtsp_host) }}" required placeholder="192.168.1.1" autocomplete="off">
          </div>
          <div class="settings-field">
            <label for="port-{{ $cam->id }}">Port</label>
            <input id="port-{{ $cam->id }}" type="number" name="rtsp_port" value="{{ old('rtsp_port.'.$cam->id, $cam->rtsp_port) }}" min="1" max="65535" required>
          </div>
          <div class="settings-field settings-field--wide">
            <label for="path-{{ $cam->id }}">Stream path</label>
            <input id="path-{{ $cam->id }}" type="text" name="rtsp_path" value="{{ old('rtsp_path.'.$cam->id, $cam->rtsp_path) }}" required placeholder="/Streaming/Channels/102">
          </div>
          <div class="settings-field">
            <label for="user-{{ $cam->id }}">Username</label>
            <input id="user-{{ $cam->id }}" type="text" name="rtsp_username" value="{{ old('rtsp_username.'.$cam->id, $cam->rtsp_username) }}" autocomplete="off">
          </div>
          <div class="settings-field">
            <label for="pass-{{ $cam->id }}">Password</label>
            <input id="pass-{{ $cam->id }}" type="password" name="rtsp_password" value="" placeholder="Leave blank to keep current" autocomplete="new-password">
          </div>
        </div>
        <div class="settings-field-check">
          <input type="hidden" name="is_enabled" value="0">
          <input id="en-cam-{{ $cam->id }}" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled.'.$cam->id, $cam->is_enabled))>
          <label for="en-cam-{{ $cam->id }}">Enable stream for this camera</label>
        </div>
        <button type="submit" class="settings-save-btn">Save {{ $cam->name }}</button>
      </form>
    </div>
  @endforeach
</article>
