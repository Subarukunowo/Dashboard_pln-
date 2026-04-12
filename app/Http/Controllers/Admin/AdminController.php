<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\ActivityLog;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalUsers'      => User::count(),
            'activeUsers'     => User::where('is_active', true)->count(),
            'pendingDevices'  => UserDevice::where('status', 'pending')->count(),
            'totalDevices'    => UserDevice::where('status', 'approved')->count(),
            'recentLogs'      => ActivityLog::with('user')->latest()->take(10)->get(),
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
        ActivityLogger::log('toggle_user', ($user->is_active ? 'Aktifkan' : 'Nonaktifkan') . ' user: ' . $user->email);
        return back()->with('success', 'Status user diperbarui.');
    }

    public function resetTwoFactor(User $user)
    {
        $user->update(['two_factor_secret' => null, 'two_factor_enabled' => false]);
        UserDevice::where('user_id', $user->id)->delete();
        ActivityLogger::log('reset_2fa', 'Reset 2FA & devices user: ' . $user->email);
        return back()->with('success', '2FA dan devices user direset.');
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
        ActivityLogger::log('approve_device', 'Approve device: ' . $device->device_name . ' untuk ' . $device->user->email);
        return back()->with('success', 'Perangkat disetujui.');
    }

    public function rejectDevice(Request $request, UserDevice $device)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $device->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason ?? 'Ditolak oleh admin.',
            'approved_by'   => auth()->id(),
        ]);
        ActivityLogger::log('reject_device', 'Reject device: ' . $device->device_name . ' untuk ' . $device->user->email);
        return back()->with('success', 'Perangkat ditolak.');
    }

    public function revokeDevice(UserDevice $device)
    {
        $device->delete();
        ActivityLogger::log('revoke_device', 'Hapus device: ' . $device->device_name . ' dari ' . $device->user->email);
        return back()->with('success', 'Perangkat dihapus.');
    }

    // ===== LOGS =====
    public function logs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action,  fn($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(20);
        $users = User::orderBy('name')->get();
        return view('admin.logs.index', compact('logs', 'users'));
    }
}