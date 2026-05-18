@extends('admin.layout')

@section('title', 'Akun & operator')

@section('content')
<div class="admin-page">
  <div class="admin-page-head">
    <div>
      <h1 class="admin-page-title">Akun &amp; operator</h1>
      <p class="admin-page-lead">Tambah, ubah, nonaktifkan, atau atur ulang kata sandi. Operator perlu dipetakan ke control room atau ruang uji.</p>
    </div>
    <a href="{{ route('admin.operators.create') }}" class="admin-btn admin-btn--primary">
      <i class="ti ti-user-plus" aria-hidden="true"></i> Tambah pengguna
    </a>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Peran</th>
          <th>Ruang / CR</th>
          <th>Status</th>
          <th class="admin-table-actions">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
          <tr class="{{ ! $u->is_active ? 'admin-table-row--muted' : '' }}">
            <td><strong>{{ $u->name }}</strong></td>
            <td>{{ $u->email }}</td>
            <td><span class="admin-pill">{{ $u->role }}</span></td>
            <td class="admin-table-sub">
              @if($u->testingRoom)
                {{ $u->testingRoom->name }}
                <span class="admin-table-hint">({{ $u->testingRoom->controlRoom?->code ?? '—' }})</span>
              @elseif($u->controlRoom)
                Seluruh {{ $u->controlRoom->name }}
              @else
                —
              @endif
            </td>
            <td>
              @if($u->is_active)
                <span class="admin-badge admin-badge--ok">Aktif</span>
              @else
                <span class="admin-badge admin-badge--off">Nonaktif</span>
              @endif
            </td>
            <td class="admin-table-actions">
              <div class="admin-row-actions">
                <a href="{{ route('admin.operators.edit', $u) }}" class="admin-btn admin-btn--sm admin-btn--ghost">Ubah</a>
                @if($u->id !== auth()->id())
                  <form method="POST" action="{{ route('admin.operators.toggle', $u) }}" class="admin-inline-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="admin-btn admin-btn--sm admin-btn--ghost">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                  </form>
                  <form method="POST" action="{{ route('admin.operators.destroy', $u) }}" class="admin-inline-form" onsubmit="return confirm(@json('Hapus permanen '.$u->name.'? Token API akan dicabut.'));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn--sm admin-btn--danger-ghost">Hapus</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="admin-table-empty">Belum ada pengguna.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
