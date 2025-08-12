<?php

/**
 * Auth — Pure PHP JWT (HS256) implementation.
 * No third-party libraries needed.
 *
 * Usage:
 *   $token = Auth::sign(['id' => 1, 'role' => 'admin']);
 *   $user  = Auth::requireRole('admin');   // call at top of any protected page
 */
class Auth
{

    private static function secret(): string
    {
        // JWT_SECRET must be set in .env — at least 32 chars
        $secret = env('JWT_SECRET', '');
        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET is not set in .env');
        }
        return $secret;
    }

    // -------------------------------------------------------------------------
    // Sign — create a JWT token
    // -------------------------------------------------------------------------
    public static function sign(array $payload, int $ttlSeconds = 86400): string
    {
        $header = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload['iat'] = time();
        $payload['exp'] = time() + $ttlSeconds;

        $body      = self::base64url(json_encode($payload));
        $signature = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));

        return "$header.$body.$signature";
    }

    // -------------------------------------------------------------------------
    // Decode — verify signature and expiry, return payload or null
    // -------------------------------------------------------------------------
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $body, $sig] = $parts;

        // Verify signature
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));
        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode(self::base64urlDecode($body), true);
        if (!is_array($payload)) return null;

        // Check expiry
        if (isset($payload['exp']) && $payload['exp'] < time()) return null;

        return $payload;
    }

    // -------------------------------------------------------------------------
    // requireRole — call at the top of every protected page via head.php
    //   - Reads token from HttpOnly cookie 'jwt'
    //   - Verifies signature + expiry + role
    //   - Redirects to login on failure
    //   - Returns the decoded payload ($user) on success
    // -------------------------------------------------------------------------
    public static function requireRole(string $role): array
    {
        $token = $_COOKIE['jwt'] ?? null;

        if ($token === null) {
            self::denyAccess();
        }

        $payload = self::decode($token);

        if ($payload === null || ($payload['role'] ?? '') !== $role) {
            self::denyAccess();
        }

        return $payload;
    }

    // -------------------------------------------------------------------------
    // setTokenCookie — write JWT to HttpOnly cookie after login
    // -------------------------------------------------------------------------
    public static function setTokenCookie(string $token, int $ttlSeconds = 86400): void
    {
        setcookie('jwt', $token, [
            'expires'  => time() + $ttlSeconds,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => isset($_SERVER['HTTPS']),   // true on HTTPS, false on local
        ]);
    }

    // -------------------------------------------------------------------------
    // clearTokenCookie — delete JWT cookie on logout
    // -------------------------------------------------------------------------
    public static function clearTokenCookie(): void
    {
        $secure = isset($_SERVER['HTTPS']);
        setcookie('jwt', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => $secure,
        ]);
        unset($_COOKIE['jwt']);
    }

    // -------------------------------------------------------------------------
    // getUser — decode the current JWT without enforcing a specific role.
    //   Returns null if no valid token is present.
    // -------------------------------------------------------------------------
    public static function getUser(): ?array
    {
        $token = $_COOKIE['jwt'] ?? null;
        if ($token === null) return null;
        return self::decode($token);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------
    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function denyAccess(): never
    {
        Response::redirect('/auth/login');
    }
}
