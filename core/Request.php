<?php

/**
 * Request — thin wrapper around $_POST / $_GET / $_SERVER
 */
class Request
{

    /** Read a POST field (trimmed, or null if missing) */
    public static function post(string $key): ?string
    {
        $val = $_POST[$key] ?? null;
        return $val !== null ? trim($val) : null;
    }

    /** Read a GET param (trimmed, or null if missing) */
    public static function get(string $key): ?string
    {
        $val = $_GET[$key] ?? null;
        return $val !== null ? trim($val) : null;
    }

    /** True when the current request is a POST */
    public static function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** True when the current request is a GET */
    public static function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /** The current request URI path, without query string and base prefix */
    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = strtok($uri, '?') ?: '/';

        // Strip the base path prefix (e.g. /new-path) when running under a subfolder
        $base = defined('APP_BASE') ? APP_BASE : '';
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        return $path;
    }
}
