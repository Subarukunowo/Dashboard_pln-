@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content_header')
    <h1>Manajemen User</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr><th>User</th><th>Perangkat</th><th>2FA</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <img src="{{ $user->avatar }}" width="32" height="32" class="rounded-circle mr-2">
                        <strong>{{ $user->name }}</strong><br>
                        <small class="text-muted">{{ $user->email }}</small>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $user->devices_count }} perangkat</span>
                    </td>
                    <td>
                        @if($user->two_factor_enabled)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Belum</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-xs {{ $user->is_active ? 'btn-warning' : 'btn-success' }}">
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.reset2fa', $user) }}" style="display:inline"
                              onsubmit="return confirm('Reset 2FA dan semua perangkat user ini?')">
                            @csrf
                            <button class="btn btn-xs btn-danger">Reset 2FA</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@stop