@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content_header')
    <h1>Manajemen User</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-valign-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Perangkat</th>
                    <th>2FA</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->avatar }}" width="40" height="40" class="rounded-circle mr-3 shadow-sm" alt="User Image">
                            <div>
                                <strong class="d-block">{{ $user->name }}</strong>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $user->devices_count ?? 0 }} perangkat</span>
                    </td>
                    <td>
                        <span class="badge {{ $user->two_factor_enabled ? 'badge-success' : 'badge-secondary' }}">
                            {{ $user->two_factor_enabled ? 'Aktif' : 'Belum' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="mr-1">
                                @csrf
                                <button class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" title="Toggle Status">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.users.reset2fa', $user) }}" 
                                  onsubmit="return confirm('Reset 2FA user ini?')">
                                @csrf
                                <button class="btn btn-sm btn-danger" title="Reset Security">
                                    Reset 2FA
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer clearfix">
            {{ $users->links() }}
        </div>
    @endif
</div>
@stop

@section('css')
    <style>
        .table-valign-middle td { vertical-align: middle !important; }
    </style>
@stop

@section('js')
    @include('admin.partials.alerts')
@stop