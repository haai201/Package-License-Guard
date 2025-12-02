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
        // TODO: đổi yourdomain.com thành domain license server thật của bạn
        $parts = [
            'aHR0cHM6Ly8=',                   
            'bGljZW5zZS4=',                   
            'eW91cmRkb21haW4uY29t',           
            'L2FwaS9saWNlbnNlL2NoZWNr'        
        ];

        $b64 = implode('', $parts);

        return base64_decode($b64);
    }

    public static function sharedSecret(): string
    {
        // TODO: đồng bộ với LICENSE_SECRET trên server, sau đó mã hoá lại các chunk này
        $chunks = [
            'c2VjcmV0',     
            'X2tleV8=',     
            'NDU2YWFi'      
        ];

        $s = '';
        foreach ($chunks as $c) {
            $s .= base64_decode($c);
        }

        return strrev($s);
    }
}
