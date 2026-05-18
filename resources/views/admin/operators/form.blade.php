@extends('admin.layout')

@section('title', $mode === 'create' ? 'Tambah pengguna' : 'Ubah pengguna')

@section('content')
@php
  $isEdit = $mode === 'edit';
@endphp
<div class="admin-page admin-page--narrow">
  <div class="admin-page-head admin-page-head--stack">
    <div>
      <h1 class="admin-page-title">{{ $isEdit ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
      <p class="admin-page-lead">{{ $isEdit ? $user->email : 'Isi data login dan akses ruang.' }}</p>
    </div>
    <a href="{{ route('admin.operators.index') }}" class="admin-btn admin-btn--ghost">← Kembali ke daftar</a>
  </div>

  <form method="POST" action="{{ $isEdit ? route('admin.operators.update', $user) : route('admin.operators.store') }}" class="admin-card admin-form">
    @csrf
    @if($isEdit)
      @method('PUT')
    @endif

    <div class="admin-form-grid">
      <div class="admin-field">
        <label for="name">Nama lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
      </div>
      <div class="admin-field">
        <label for="email">Email (login)</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
      </div>
      @if(! $isEdit)
        <div class="admin-field">
          <label for="password">Kata sandi awal</label>
          <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
        </div>
        <div class="admin-field">
          <label for="password_confirmation">Ulangi kata sandi</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8">
        </div>
      @endif
      <div class="admin-field">
        <label for="role">Peran</label>
        <select id="role" name="role" required>
          @foreach (['admin' => 'Admin', 'operator' => 'Operator', 'viewer' => 'Viewer'] as $val => $label)
            <option value="{{ $val }}" @selected(old('role', $user->role ?? 'operator') === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="admin-field admin-field--full">
        <label for="testing_room_id">Ruang uji (disarankan)</label>
        <select id="testing_room_id" name="testing_room_id">
          <option value="">— Tidak —</option>
          @foreach ($testingRooms as $tr)
            <option value="{{ $tr->id }}" @selected((string) old('testing_room_id', $user->testing_room_id) === (string) $tr->id)>
              {{ $tr->controlRoom->code ?? 'CR' }} · {{ $tr->name }} ({{ $tr->type }})
            </option>
          @endforeach
        </select>
        <p class="admin-field-hint">Untuk operator satu ruang, pilih ruang uji. Model akan mengisi control room otomatis.</p>
      </div>
      <div class="admin-field admin-field--full">
        <label for="control_room_id">Control room (akses lebar)</label>
        <select id="control_room_id" name="control_room_id">
          <option value="">— Tidak —</option>
          @foreach ($controlRooms as $cr)
            <option value="{{ $cr->id }}" @selected((string) old('control_room_id', $user->control_room_id) === (string) $cr->id)>
              {{ $cr->code }} — {{ $cr->name }}
            </option>
          @endforeach
        </select>
        <p class="admin-field-hint">Alternatif: akses semua ruang dalam satu control room (tanpa ruang uji spesifik).</p>
      </div>
      <div class="admin-field admin-field--full admin-field--check">
        <label class="admin-check">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
          <span>Akun aktif (bisa login)</span>
        </label>
      </div>
    </div>

    <div class="admin-form-actions">
      <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Simpan perubahan' : 'Buat akun' }}</button>
    </div>
  </form>

  @if($isEdit)
    <div class="admin-card admin-form admin-mt">
      <h2 class="admin-section-title">Atur ulang kata sandi</h2>
      <p class="admin-page-lead admin-page-lead--tight">Kirim kata sandi baru untuk pengguna ini. Mereka tidak perlu tahu sandi lama.</p>
      <form method="POST" action="{{ route('admin.operators.password', $user) }}" class="admin-form-grid">
        @csrf
        @method('PATCH')
        <div class="admin-field">
          <label for="pw_new">Kata sandi baru</label>
          <input type="password" id="pw_new" name="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="admin-field">
          <label for="pw_new2">Konfirmasi</label>
          <input type="password" id="pw_new2" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </div>
        <div class="admin-form-actions admin-field--full">
          <button type="submit" class="admin-btn admin-btn--secondary">Perbarui kata sandi</button>
        </div>
      </form>
    </div>
  @endif
</div>
@endsection
