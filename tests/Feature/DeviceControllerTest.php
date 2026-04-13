<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDevice;
use App\Mail\DeviceApprovedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeviceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_device()
    {
        // Biar kalau ada error kodingan di Controller langsung muncul detailnya di terminal
        $this->withoutExceptionHandling();
        
        // Lewati middleware auth/admin karena kita fokus ngetes logika internal
        $this->withoutMiddleware();

        Mail::fake();

        // 1. Persiapan Data
        $user = User::factory()->create();
        
        $device = UserDevice::create([
            'user_id'    => $user->id,
            'device_name' => 'Laptop Acer Nitro 5',
            'device_token'=> 'random_token_123',
            'ip_address'  => '127.0.0.1',
            'status'      => 'pending',
            'requested_at'=> now(),
        ]);

        // 2. Eksekusi Aksi (Simulasi Admin klik tombol Approve)
        $response = $this->actingAs($user)->patch(route('devices.approve', $device->id));

        // 3. Verifikasi Database: Pastikan status berubah
        $this->assertDatabaseHas('user_devices', [
            'id'     => $device->id,
            'status' => 'approved',
        ]);

        // 4. Verifikasi Email: Pastikan notifikasi terkirim
        Mail::assertSent(DeviceApprovedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // 5. Verifikasi Redirect: Pastikan kembali ke halaman sebelumnya
        $response->assertStatus(302);
    }
}