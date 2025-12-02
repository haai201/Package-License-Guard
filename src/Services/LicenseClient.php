<?php

namespace Susoft\LicenseGuard\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Susoft\LicenseGuard\Support\Obfuscator;

class LicenseClient
{
    protected const CACHE_KEY = 'license_guard_token';

    public static function check(Request $request): bool
    {
        if (config('license_guard.disabled', false)) {
            return true;
        }

        $licenseKey  = config('license_guard.key');
        $productCode = config('license_guard.product_code');

        if (empty($licenseKey) || empty($productCode)) {
            return false;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached) && ($cached['exp'] ?? 0) > time()) {
            return true;
        }

        $payload = [
            'k'  => $licenseKey,
            'pc' => $productCode,
            'd'  => $request->getHost(),
            'i'  => $request->ip(),
            's'  => php_uname(),
            't'  => time(),
        ];

        $encoded = Obfuscator::pack($payload);
        $url     = Obfuscator::licenseEndpoint();

        try {
            $response = Http::timeout(5)->post($url, [
                'p' => $encoded,
            ]);

            if (! $response->ok()) {
                return false;
            }

            $data = $response->json();

            if (! is_array($data) || ($data['status'] ?? null) !== 'ok') {
                return false;
            }

            $token = $data['token'] ?? '';
            if (! self::verifyToken($token)) {
                return false;
            }

            $ttl = (int) ($data['ttl'] ?? config('license_guard.cache_ttl', 300));

            Cache::put(self::CACHE_KEY, [
                'token' => $token,
                'exp'   => time() + $ttl,
            ], $ttl);

            return true;
        } catch (\Throwable $e) {
            if (config('license_guard.grace_on_error', false)) {
                return true;
            }

            return false;
        }
    }

    protected static function verifyToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        [$body, $mac] = explode('.', $token) + [null, null];
        if (! $body || ! $mac) {
            return false;
        }

        $secret = Obfuscator::sharedSecret();
        $calc   = hash_hmac('sha256', $body, $secret);

        if (! hash_equals($calc, $mac)) {
            return false;
        }

        $json = base64_decode($body, true);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return false;
        }

        if (($data['exp'] ?? 0) < time()) {
            return false;
        }

        return true;
    }
}
