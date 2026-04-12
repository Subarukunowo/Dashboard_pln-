@extends('adminlte::page')

@section('title', 'Manajemen Perangkat')

@section('content_header')
    <h1>Manajemen Perangkat</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ $status === 'pending'  ? 'active' : '' }}" href="?status=pending">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" href="?status=approved">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="?status=rejected">Rejected</a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User</th><th>Perangkat</th><th>IP</th>
                    <th>Diminta</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                <tr>
                    <td>
                        <strong>{{ $device->user->name }}</strong><br>
                        <small class="text-muted">{{ $device->user->email }}</small>
                    </td>
                    <td>{{ $device->device_name }}</td>
                    <td>{{ $device->ip_address }}</td>
                    <td>{{ $device->requested_at?->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($device->isPending())
                            <span class="badge badge-warning">Pending</span>
                        @elseif($device->isApproved())
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                    <td>
                        @if($device->isPending())
                            <form method="POST" action="{{ route('admin.devices.approve', $device) }}" style="display:inline">
                                @csrf
                                <button class="btn btn-xs btn-success">✓ Approve</button>
                            </form>
                            <button class="btn btn-xs btn-danger" data-toggle="modal" data-target="#rejectModal{{ $device->id }}">✗ Reject</button>

                            {{-- Modal Reject --}}
                            <div class="modal fade" id="rejectModal{{ $device->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.devices.reject', $device) }}">
                                            @csrf
                                            <div class="modal-header"><h4 class="modal-title">Alasan Penolakan</h4></div>
                                            <div class="modal-body">
                                                <input type="text" name="reason" class="form-control" placeholder="Alasan (opsional)">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($device->isApproved())
                            <form method="POST" action="{{ route('admin.devices.revoke', $device) }}" style="display:inline"
                                  onsubmit="return confirm('Cabut akses perangkat ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-warning">Cabut</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $devices->links() }}</div>
</div>
@stop