<x-mail::message>
# ✅ Perangkat Anda Disetujui

Halo **{{ $user->name }}**,

Admin telah **menyetujui** akses untuk perangkat Anda. Silakan login kembali.

<x-mail::panel>
**Perangkat** : {{ $device->device_name }}
**Disetujui** : {{ $device->approved_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
</x-mail::panel>

<x-mail::button :url="$loginUrl" color="success">
Login Sekarang
</x-mail::button>

Jika Anda tidak merasa melakukan request ini, segera hubungi admin.

---
*Email otomatis dari sistem {{ config('app.name') }}. Jangan balas email ini.*
</x-mail::message>