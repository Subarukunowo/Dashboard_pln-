<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders
 *
 * Menambahkan HTTP security headers ke setiap response:
 * - X-Frame-Options        : cegah clickjacking
 * - X-Content-Type-Options : cegah MIME sniffing
 * - Referrer-Policy        : batasi referrer info ke pihak ketiga
 * - Permissions-Policy     : nonaktifkan fitur browser yang tidak dipakai
 * - X-XSS-Protection       : aktifkan XSS filter browser lama
 * - Content-Security-Policy: whitelist sumber konten (disesuaikan dengan CDN yang dipakai)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Jangan tambahkan ke response binary (download file)
        if ($response->headers->get('Content-Type') === 'application/octet-stream') {
            return $response;
        }

        $response->headers->set('X-Frame-Options',         'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options',  'nosniff');
        $response->headers->set('X-XSS-Protection',        '1; mode=block');
        $response->headers->set('Referrer-Policy',          'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy',       'camera=(), microphone=(), geolocation=(), payment=()');

        // CSP — sesuaikan dengan CDN/fonts yang dipakai di blade
$csp = implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com",
    "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com data:",
    "img-src 'self' data: https://lh3.googleusercontent.com https://ui-avatars.com https://api.qrserver.com",
    "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    "frame-ancestors 'self'",
    "base-uri 'self'",
    "form-action 'self'",
]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
