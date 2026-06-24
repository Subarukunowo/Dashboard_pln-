<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ActivityLogger;
use App\Mail\DeviceApprovedMail;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalUsers'     => User::count(),
            'activeUsers'    => User::where('is_active', true)->count(),
            'pendingDevices' => UserDevice::where('status', 'pending')->count(),
            'totalDevices'   => UserDevice::where('status', 'approved')->count(),
            'recentLogs'     => ActivityLog::with('user')->latest()->take(10)->get(),
        ]);
    }

    // ===== USERS =====
    public function users()
    {
        $users = User::with('devices')->withCount('devices')->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLogger::log('toggle_user', "User {$user->email} $status");
        return back()->with('success', "Akun {$user->name} berhasil $status.");
    }

    public function resetTwoFactor(User $user)
    {
        $user->update(['two_factor_secret' => null, 'two_factor_enabled' => false]);
        UserDevice::where('user_id', $user->id)->delete();
        ActivityLogger::log('reset_2fa', "Reset 2FA & devices: {$user->email}");
        return back()->with('success', "2FA dan semua perangkat {$user->name} berhasil direset.");
    }

    // ===== DEVICES =====
    public function devices(Request $request)
    {
        $status  = $request->get('status', 'pending');
        $devices = UserDevice::with('user')
            ->where('status', $status)
            ->latest('requested_at')
            ->paginate(15);
        return view('admin.devices.index', compact('devices', 'status'));
    }

    public function approveDevice(UserDevice $device)
    {
        $device->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        ActivityLogger::log('approve_device', "Approve device: {$device->device_name} untuk {$device->user->email}");

        try {
            Mail::to($device->user->email)->send(new DeviceApprovedMail($device));
        } catch (\Exception $e) {
            Log::error("Gagal kirim email approved: " . $e->getMessage());
        }

        return back()->with('success', "Perangkat {$device->device_name} milik {$device->user->name} berhasil disetujui. Email notifikasi telah dikirim.");
    }

    public function rejectDevice(Request $request, UserDevice $device)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $device->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason ?? 'Ditolak oleh admin.',
            'approved_by'   => auth()->id(),
        ]);

        ActivityLogger::log('reject_device', "Reject device: {$device->device_name} untuk {$device->user->email}");

        return back()->with('success', "Perangkat {$device->device_name} milik {$device->user->name} berhasil ditolak.");
    }

    public function revokeDevice(UserDevice $device)
    {
        $name     = $device->device_name;
        $userName = $device->user->name;
        $device->delete();

        ActivityLogger::log('revoke_device', "Hapus device: $name dari $userName");

        return back()->with('success', "Perangkat $name milik $userName berhasil dicabut aksesnya.");
    }

    // ===== LOGS =====
    public function logs(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'action'  => 'nullable|string|max:100|alpha_dash',
        ]);

        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', (int) $request->user_id))
            ->when($request->action,  fn($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(20);

        $users = User::orderBy('name')->get();

        return view('admin.logs.index', compact('logs', 'users'));
    }
}