<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use App\Mail\DeviceApprovedMail; // Pastikan sudah buat Mailable ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Tampilan daftar perangkat untuk Admin (Manage)
     */
    public function manage()
    {
        // Ambil semua perangkat yang butuh approval
        $pendingDevices = UserDevice::with('user')
            ->where('status', 'pending')
            ->get();

        return view('admin.devices.manage', compact('pendingDevices'));
    }

    /**
     * Fungsi untuk Approve Perangkat
     */
    public function approve($id)
    {
        try {
            $device = UserDevice::with('user')->findOrFail($id);
            
            $device->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // KIRIM EMAIL KE USER
            // Alur: Admin approve -> Email masuk ke user
            Mail::to($device->user->email)->send(new DeviceApprovedMail($device));

            return back()->with('success', 'Perangkat ' . $device->device_name . ' telah disetujui. Notifikasi email telah dikirim ke user.');
            
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
        $device = UserDevice::findOrFail($id);
        $device->delete();

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
    public function content(): Content
{
    return new Content(
        view: 'emails.device_approved',
        with: [
            'userName' => $this->device->user->name,
            'deviceName' => $this->device->device_name,
        ],
    );
}
}