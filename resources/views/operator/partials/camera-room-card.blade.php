@php
  $rid = $room->id;
  $hl = $highlightRoomId !== null && (int) $highlightRoomId === (int) $rid;
@endphp
<article class="settings-card {{ $hl ? 'settings-card--highlight' : '' }}" id="room-{{ $rid }}">
  <header class="settings-card-head">
    <h3 class="settings-card-room">{{ $room->name }}</h3>
  </header>

  <div class="cam-slots-grid">
    @foreach ($room->roomCameras->sortBy('slot') as $cam)
      <div class="cam-slot-card {{ $cam->is_enabled ? 'cam-slot-card--on' : '' }}">
        <div class="cam-slot-card__head">
          <span class="cam-slot-card__name">{{ $cam->name }}</span>
          <span class="cam-slot-badge">Slot {{ $cam->slot }}</span>
        </div>
        <form method="POST" action="{{ route('settings.cameras.update', $cam) }}" class="cam-slot-form">
          @csrf
          @method('PUT')
          <input
            type="url"
            name="stream_url"
            value="{{ old('stream_url.'.$cam->id, $cam->stream_url) }}"
            placeholder="http://192.168.1.x:5001/video_feed"
            autocomplete="off"
            class="cam-slot-input"
            aria-label="URL stream {{ $cam->name }}"
          >
          <div class="cam-slot-footer">
            <label class="cam-slot-toggle">
              <input type="hidden" name="is_enabled" value="0">
              <input
                type="checkbox"
                name="is_enabled"
                value="1"
                id="en-cam-{{ $cam->id }}"
                @checked(old('is_enabled.'.$cam->id, $cam->is_enabled))
                class="cam-slot-checkbox"
              >
              <span class="cam-slot-toggle__track"></span>
              <span class="cam-slot-toggle__label">{{ $cam->is_enabled ? 'Aktif' : 'Nonaktif' }}</span>
            </label>
            <button type="submit" class="cam-slot-save">Simpan</button>
          </div>
        </form>
      </div>
    @endforeach
  </div>
</article>
