@extends('admin.layout')

@section('title', $mode === 'create' ? 'Add user' : 'Edit user')

@section('content')
@php
  $isEdit = $mode === 'edit';
@endphp
<div class="admin-page admin-page--narrow">
  <div class="admin-page-head admin-page-head--stack">
    <div>
      <h1 class="admin-page-title">{{ $isEdit ? 'Edit user' : 'Add user' }}</h1>
      <p class="admin-page-lead">{{ $isEdit ? $user->email : 'Enter login credentials and room access.' }}</p>
    </div>
    <div class="admin-page-head-actions">
      <a href="{{ route('admin.operators.index') }}" class="admin-btn admin-btn--ghost">
        <i class="ti ti-arrow-left" aria-hidden="true"></i> Back
      </a>
    </div>
  </div>

  <form method="POST" action="{{ $isEdit ? route('admin.operators.update', $user) : route('admin.operators.store') }}" class="admin-card admin-form">
    @csrf
    @if($isEdit)
      @method('PUT')
    @endif

    <div class="admin-form-grid">
      <div class="admin-field">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
      </div>
      <div class="admin-field">
        <label for="email">Email (login)</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
      </div>
      @if(! $isEdit)
        <div class="admin-field">
          <label for="password">Initial password</label>
          <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
        </div>
        <div class="admin-field">
          <label for="password_confirmation">Confirm password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8">
        </div>
      @endif
      <div class="admin-field">
        <label for="role">Role</label>
        <select id="role" name="role" required>
          @foreach (['admin' => 'Admin', 'operator' => 'Operator', 'viewer' => 'Viewer'] as $val => $label)
            <option value="{{ $val }}" @selected(old('role', $user->role ?? 'operator') === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="admin-field admin-field--full">
        <label for="testing_room_id">Testing room (recommended)</label>
        <select id="testing_room_id" name="testing_room_id">
          <option value="">— None —</option>
          @foreach ($testingRooms as $tr)
            <option value="{{ $tr->id }}" @selected((string) old('testing_room_id', $user->testing_room_id) === (string) $tr->id)>
              {{ $tr->controlRoom->code ?? 'CR' }} · {{ $tr->name }} ({{ $tr->type }})
            </option>
          @endforeach
        </select>
        <p class="admin-field-hint">For a single-room operator, select a testing room. The control room is filled in automatically.</p>
      </div>
      <div class="admin-field admin-field--full">
        <label for="control_room_id">Control room (broader access)</label>
        <select id="control_room_id" name="control_room_id">
          <option value="">— None —</option>
          @foreach ($controlRooms as $cr)
            <option value="{{ $cr->id }}" @selected((string) old('control_room_id', $user->control_room_id) === (string) $cr->id)>
              {{ $cr->code }} — {{ $cr->name }}
            </option>
          @endforeach
        </select>
        <p class="admin-field-hint">Alternatively: access all rooms in one control room (no specific testing room).</p>
      </div>
      <div class="admin-field admin-field--full admin-field--check">
        <label class="admin-check">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
          <span>Active account (can log in)</span>
        </label>
      </div>
    </div>

    <div class="admin-form-actions">
      <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Save changes' : 'Create account' }}</button>
    </div>
  </form>

  @if($isEdit)
    <div class="admin-card admin-form admin-mt">
      <h2 class="admin-section-title">Reset password</h2>
      <p class="admin-page-lead admin-page-lead--tight">Set a new password for this user. They do not need to know the old one.</p>
      <form method="POST" action="{{ route('admin.operators.password', $user) }}" class="admin-form-grid">
        @csrf
        @method('PATCH')
        <div class="admin-field">
          <label for="pw_new">New password</label>
          <input type="password" id="pw_new" name="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="admin-field">
          <label for="pw_new2">Confirm</label>
          <input type="password" id="pw_new2" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </div>
        <div class="admin-form-actions admin-field--full">
          <button type="submit" class="admin-btn admin-btn--secondary">Update password</button>
        </div>
      </form>
    </div>
  @endif
</div>
@endsection
