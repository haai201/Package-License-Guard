<?php

namespace Susoft\LicenseGuard\Support;

class Obfuscator
{
    public static function pack(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $gz   = gzdeflate($json, 9);
        $b64  = base64_encode($gz);

        return strrev($b64);
    }

    public static function unpack(string $str): array
    {
        $b64  = strrev($str);
        $gz   = base64_decode($b64, true);
        if ($gz === false) {
            return [];
        }

        $json = @gzinflate($gz);
        if ($json === false) {
            return [];
        }

        return json_decode($json, true) ?: [];
    }

    public static function licenseEndpoint(): string
    {
        $parts = [
            'aHR0cHM6Ly8=',
            'bGljZW5zZS4=',
            'dnRjbmV0dmlldC5jb20=',
            'L2FwaS9saWNlbnNlL3ZlcmlmeQ=='
        ];

        $url = '';

        foreach ($parts as $p) {
            $url .= base64_decode($p);
        }

        return $url;
    }


    public static function sharedSecret(): string
    {
        $chunks = [
            'OFlrMlVoM0RuMkZq',
            'czgyaHNLOTEyaEgx=',
            'OXNoQTkySHM='
        ];

        $s = '';
        foreach ($chunks as $c) {
            $s .= base64_decode($c);
        }

        return strrev($s);
    }

}
