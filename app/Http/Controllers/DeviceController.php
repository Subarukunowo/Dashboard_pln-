<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use App\Mail\DeviceApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Tampilan daftar perangkat untuk User (Personal)
     */
  public function index(Request $request)
    {
        $devices = UserDevice::where('user_id', Auth::id())
            ->orderBy('last_seen_at', 'desc')
            ->get();

        // HARUS SAMA DENGAN AuthController!
        $currentToken = hash('sha256', 
            $request->userAgent() . 
            $request->header('Accept-Language')
        );

        return view('devices.index', compact('devices', 'currentToken'));
    }

    /**
     * Tampilan daftar perangkat untuk Admin (Manage)
     */
    public function manage()
    {
        $pendingDevices = UserDevice::with('user')
            ->where('status', 'pending')
            ->get();

        return view('admin.devices.manage', compact('pendingDevices'));
    }

    /**
     * Fungsi untuk Approve Perangkat (Admin)
     */
    public function approve($id)
    {
        try {
            $device = UserDevice::with('user')->findOrFail($id);
            
            $device->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Kirim email notifikasi ke user
            Mail::to($device->user->email)->send(new DeviceApprovedMail($device));

            return back()->with('success', 'Perangkat ' . $device->device_name . ' telah disetujui.');
            
        } catch (\Exception $e) {
            Log::error("Gagal approve perangkat: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyetujui perangkat.');
        }
    }

    /**
     * Fungsi hapus/reject perangkat
     */
    public function destroy($id)
    {
        // Cari perangkat tanpa batasan Auth::id() agar Admin bisa hapus juga
        $device = UserDevice::findOrFail($id);
        $device->delete();

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
}