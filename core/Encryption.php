<?php

class Encryption
{
    private static function key(): string
    {
        $k = $_ENV['ENCRYPTION_KEY'] ?? '';
        if ($k === '') {
            throw new RuntimeException('ENCRYPTION_KEY is not set in .env');
        }
        return base64_decode($k);
    }

    public static function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', self::key(), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        $raw = base64_decode($value, true);
        if ($raw === false || strlen($raw) < 17) return $value;
        $iv        = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        $result    = openssl_decrypt($encrypted, 'AES-256-CBC', self::key(), 0, $iv);
        return $result === false ? $value : $result;
    }
}
