{{--
    Partial: @include('partials.avatar', ['user' => $user, 'size' => 'sm'])

    Props:
      $user  — App\Models\User  (default: auth()->user())
      $size  — 'xs' | 'sm' | 'md' | 'lg' | 'xl'  (default: 'md')
--}}

@php
    $avatarUser = $user ?? auth()->user();
    $avatarSize = $size ?? 'md';

    // URL avatar dari kolom DB
    $avatarUrl  = $avatarUser->avatar ?? null;

    // Initials dari nama
    $parts    = explode(' ', trim($avatarUser->name ?? 'U'));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));

    // Warna background konsisten per-user (hash nama)
    $palette = ['#e67e22','#2ecc71','#3498db','#9b59b6','#e74c3c','#1abc9c','#f39c12'];
    $bg      = $palette[abs(crc32($avatarUser->name ?? '')) % count($palette)];

    // Inline style: inject CSS custom properties
    $inlineStyle = "--avatar-bg:{$bg};";
    if ($avatarUrl) {
        $inlineStyle .= "--avatar-url:url('{$avatarUrl}');";
    }
@endphp

<div
    class="avatar avatar--{{ $avatarSize }}"
    style="{{ $inlineStyle }}"
    data-initials="{{ $initials }}"
    aria-label="{{ $avatarUser->name ?? 'User' }}"
    role="img"
></div>