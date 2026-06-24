<?php

namespace App\Helpers;

class InternalApiToken
{
    public static function generate(): array
    {
        $secret    = config("app.internal_api_secret", "");
        $timestamp = (string) time();
        $token     = hash_hmac("sha256", $timestamp, $secret);

        return [$token, $timestamp];
    }
}