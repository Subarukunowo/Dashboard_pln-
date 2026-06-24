<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * InternalApiOnly
 *
 * Memvalidasi bahwa setiap request ke API internal membawa
 * HMAC-signed token yang di-generate oleh server sendiri (via blade).
 *
 * Token format : HMAC-SHA256(secret, "timestamp")
 * Token valid  : 60 detik dari waktu generate (anti-replay)
 *
 * Tanpa token valid -> 404, bukan 401/403, supaya endpoint
 * tidak kelihatan "ada tapi diprotect" dari browser / Postman / curl.
 */
class InternalApiOnly
{
    /** Toleransi drift waktu (detik) */
    private const TTL = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('app.internal_api_secret');

        // Jika secret belum di-set di .env -> tolak semua akses
        if (empty($secret)) {
            abort(404);
        }

        $token     = $request->header('X-Api-Token');
        $timestamp = $request->header('X-Api-Timestamp');

        // Header tidak ada sama sekali -> 404
        if (!$token || !$timestamp) {
            abort(404);
        }

        // Timestamp harus angka dan dalam rentang TTL
        if (!ctype_digit((string) $timestamp)) {
            abort(404);
        }

        $diff = abs(time() - (int) $timestamp);
        if ($diff > self::TTL) {
            abort(404);
        }

        // Verifikasi HMAC
        $expected = hash_hmac('sha256', (string) $timestamp, $secret);

        if (!hash_equals($expected, $token)) {
            abort(404);
        }

        return $next($request);
    }
}
