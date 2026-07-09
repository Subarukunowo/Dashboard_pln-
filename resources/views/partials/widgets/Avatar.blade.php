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

@if ($avatarUrl)
    <img
        class="avatar avatar--{{ $avatarSize }}"
        src="{{ $avatarUrl }}"
        alt="{{ $avatarUser->name ?? 'User' }}"
        style="{{ $inlineStyle }}"
    />
@else
    <svg
        class="avatar avatar--{{ $avatarSize }}"
        style="{{ $inlineStyle }}"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 100"
        role="img"
        aria-label="{{ $avatarUser->name ?? 'User' }}"
    >
        <rect width="100" height="100" fill="var(--avatar-bg)" />
        <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="#fff" font-family="Arial, Helvetica, sans-serif" font-size="40">{{ $initials }}</text>
    </svg>
@endif