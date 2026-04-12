@component('mail::message')
# Request Perangkat Baru

**{{ $user->name }}** ({{ $user->email }}) meminta akses dari perangkat baru:

**Perangkat:** {{ $deviceName }}

Silakan login ke panel admin untuk menyetujui atau menolak request ini.

@component('mail::button', ['url' => url('/admin/devices')])
Buka Panel Admin
@endcomponent

@endcomponent