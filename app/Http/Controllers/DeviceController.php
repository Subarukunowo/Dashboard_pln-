<?php

namespace App\Http\Controllers;

use App\Mail\DeviceApprovedMail;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DeviceController extends Controller
{
    /**
     * Daftar perangkat milik user yang sedang login
     * Route: GET /devices
     */
    public function index(Request $request)
    {
        $devices = UserDevice::where('user_id', Auth::id())
            ->orderBy('last_seen_at', 'desc')
            ->get();

        $currentToken = hash('sha256',
            $request->userAgent() .
            $request->header('Accept-Language')
        );

        return view('devices.index', compact('devices', 'currentToken'));
    }

    /**
     * Daftar semua perangkat pending untuk admin
     * Route: GET /admin/devices/manage
     */
    public function manage()
    {
        $pendingDevices = UserDevice::with('user')
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.devices.manage', compact('pendingDevices'));
    }

    /**
     * Setujui perangkat dan kirim email ke user
     * Route: PATCH /admin/devices/{id}/approve
     */
    public function approve($id)
    {
        try {
            $device = UserDevice::with('user')->findOrFail($id);

            if ($device->status !== 'pending') {
                return back()->with('warning', 'Perangkat ini sudah diproses sebelumnya.');
            }

            $device->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            Mail::to($device->user->email)->queue(new DeviceApprovedMail($device));

            return back()->with('success', "Perangkat \"{$device->device_name}\" milik {$device->user->name} telah disetujui.");

        } catch (\Exception $e) {
            Log::error('Gagal approve perangkat', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyetujui perangkat.');
        }
    }

    /**
     * Tolak perangkat (tanpa email — sesuai kebutuhan yang ada)
     * Route: POST /admin/devices/{device}/reject
     */
    public function reject(Request $request, $id)
    {
        try {
            $device = UserDevice::with('user')->findOrFail($id);

            if ($device->status !== 'pending') {
                return back()->with('warning', 'Perangkat ini sudah diproses sebelumnya.');
            }

            $device->update([
                'status'        => 'rejected',
                'rejected_at'   => now(),
                'reject_reason' => $request->input('reason', ''),
            ]);

            return back()->with('success', "Perangkat \"{$device->device_name}\" telah ditolak.");

        } catch (\Exception $e) {
            Log::error('Gagal reject perangkat', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menolak perangkat.');
        }
    }

    /**
     * Hapus perangkat (admin atau user pemilik)
     * Route: DELETE /admin/devices/{device}/revoke  (sudah ada di web.php)
     */
    public function destroy($id)
    {
        $device = UserDevice::findOrFail($id);
        $device->delete();

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
}