@extends('admin.layout')

@section('title', 'Log register PLC')

@section('content')
<div class="admin-page">
  <div class="admin-page-head">
    <div>
      <h1 class="admin-page-title">Log register PLC</h1>
      <p class="admin-page-lead">Riwayat perubahan nilai register dari bridge. Filter opsional per ruang atau tipe.</p>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="admin-filters admin-card">
    <div class="admin-filters-grid">
      <div class="admin-field">
        <label for="testing_room_id">Ruang uji</label>
        <select id="testing_room_id" name="testing_room_id" onchange="this.form.submit()">
          <option value="">Semua</option>
          @foreach ($rooms as $tr)
            <option value="{{ $tr->id }}" @selected((string) request('testing_room_id') === (string) $tr->id)>
              {{ $tr->controlRoom->code ?? '' }} · {{ $tr->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="admin-field">
        <label for="change_type">Tipe perubahan</label>
        <select id="change_type" name="change_type" onchange="this.form.submit()">
          <option value="">Semua</option>
          @foreach ($changeTypes as $ct)
            <option value="{{ $ct }}" @selected(request('change_type') === $ct)>{{ $ct }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </form>

  <div class="admin-table-wrap admin-mt">
    <table class="admin-table admin-table--compact">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Ruang</th>
          <th>Register</th>
          <th>Nilai</th>
          <th>Tipe</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($logs as $log)
          <tr>
            <td class="admin-nowrap">{{ $log->occurred_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') }}</td>
            <td>{{ $log->testingRoom->name ?? '—' }}</td>
            <td>
              <span class="admin-mono">{{ $log->register_name ?: $log->register_address }}</span>
              @if($log->register_description)
                <span class="admin-table-hint">{{ \Illuminate\Support\Str::limit($log->register_description, 40) }}</span>
              @endif
            </td>
            <td class="admin-table-sub">
              @if($log->old_value !== null || $log->new_value !== null)
                <span class="admin-mono">{{ $log->old_value }} → {{ $log->new_value }}</span>
              @elseif($log->decoded_value !== null)
                <span class="admin-mono">{{ is_array($log->decoded_value) ? json_encode($log->decoded_value) : $log->decoded_value }}</span>
              @else
                —
              @endif
            </td>
            <td><span class="admin-pill admin-pill--muted">{{ $log->change_type ?? '—' }}</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="admin-table-empty">Belum ada entri log.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="admin-pagination">
    {{ $logs->links('vendor.pagination.admin') }}
  </div>
</div>
@endsection
