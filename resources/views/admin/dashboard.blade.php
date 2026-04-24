@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
@stop

{{-- ✅ FAVICON + CUSTOM CSS --}}
@push('css')
    {{-- 🔥 GLOBAL FAVICON (Embedded Direct - No Cache) --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/favicons/favicon-96x96.png') }}?v={{ time() }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicons/favicon.svg') }}?v={{ time() }}" />
    <link rel="shortcut icon" href="{{ asset('assets/favicons/favicon.ico') }}?v={{ time() }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicons/apple-touch-icon.png') }}?v={{ time() }}" />
    <link rel="manifest" href="{{ asset('assets/favicons/site.webmanifest') }}?v={{ time() }}" />
    
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
    {{-- 1. Widget Row (Statistik) --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total User</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">
                    Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activeUsers }}</h3>
                    <p>User Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">
                    Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingDevices }}</h3>
                    <p>Device Pending</p>
                </div>
                <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                <a href="{{ route('admin.devices') }}" class="small-box-footer">
                    Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalDevices }}</h3>
                    <p>Device Approved</p>
                </div>
                <div class="icon"><i class="fas fa-laptop"></i></div>
                <a href="{{ route('admin.devices') }}?status=approved" class="small-box-footer">
                    Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Aktivitas Terbaru Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Aktivitas Terbaru</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                    <tr>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td><span class="badge badge-info">{{ $log->action }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.alerts')
@stop