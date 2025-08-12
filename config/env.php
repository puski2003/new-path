<?php

/**
 * Environment Configuration
 * Loads from .env file if present, otherwise falls back to defaults.
 */

$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Helper — read from $_ENV with a fallback default
function env(string $key, string $default = ''): string
{
    return $_ENV[$key] ?? $default;
}
