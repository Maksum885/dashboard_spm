@extends('admin.layout')

@section('title', 'User accounts')

@section('content')
<div class="admin-page">
  <div class="admin-page-head">
    <div>
      <h1 class="admin-page-title">User accounts</h1>
      <p class="admin-page-lead">Add, edit, deactivate, or reset passwords. Operators must be mapped to a control room or testing room.</p>
    </div>
    <div class="admin-page-head-actions">
      <a href="{{ route('dashboard') }}" class="admin-btn admin-btn--ghost">
        <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to dashboard
      </a>
      <a href="{{ route('admin.operators.create') }}" class="admin-btn admin-btn--primary">
        <i class="ti ti-user-plus" aria-hidden="true"></i> Add user
      </a>
    </div>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Room / CR</th>
          <th>Status</th>
          <th class="admin-table-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
          <tr class="{{ ! $u->is_active ? 'admin-table-row--muted' : '' }}">
            <td><strong>{{ $u->name }}</strong></td>
            <td>{{ $u->email }}</td>
            <td><span class="admin-pill">{{ $u->role }}</span></td>
            <td class="admin-table-sub admin-table-col-room">
              @if($u->testingRoom)
                {{ $u->testingRoom->name }}
                <span class="admin-table-hint">({{ $u->testingRoom->controlRoom?->code ?? '—' }})</span>
              @elseif($u->controlRoom)
                All {{ $u->controlRoom->name }}
              @else
                —
              @endif
            </td>
            <td>
              @if($u->is_active)
                <span class="admin-badge admin-badge--ok">Active</span>
              @else
                <span class="admin-badge admin-badge--off">Inactive</span>
              @endif
            </td>
            <td class="admin-table-actions">
              <div class="admin-row-actions">
                <a href="{{ route('admin.operators.edit', $u) }}" class="admin-btn admin-btn--sm admin-btn--ghost">Edit</a>
                @if($u->id !== auth()->id())
                  <form method="POST" action="{{ route('admin.operators.toggle', $u) }}" class="admin-inline-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="admin-btn admin-btn--sm admin-btn--ghost">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                  </form>
                  <form method="POST" action="{{ route('admin.operators.destroy', $u) }}" class="admin-inline-form" onsubmit="return confirm(@json('Permanently delete '.$u->name.'? API tokens will be revoked.'));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn--sm admin-btn--danger-ghost">Delete</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="admin-table-empty">No users yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
