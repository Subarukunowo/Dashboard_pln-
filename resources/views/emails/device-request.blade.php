<x-mail::message>
# 🔔 Request Perangkat Baru

Halo Admin,

Pengguna berikut mencoba login dari **perangkat baru** dan membutuhkan persetujuan Anda.

<x-mail::panel>
**Nama**&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $user->name }}
**Email**&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $user->email }}
**Perangkat** : {{ $device->device_name }}
**IP Address** : {{ $device->ip_address }}
**Waktu**&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $device->requested_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
</x-mail::panel>

<x-mail::button :url="$approveUrl" color="success">
✓ Setujui Perangkat
</x-mail::button>

<x-mail::button :url="$rejectUrl" color="error">
✗ Tolak Perangkat
</x-mail::button>

Atau kelola semua request di [Panel Admin]({{ $panelUrl }}).

---
*Email otomatis dari sistem {{ config('app.name') }}. Jangan balas email ini.*
</x-mail::message>