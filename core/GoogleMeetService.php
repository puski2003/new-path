<?php

/**
 * GoogleMeetService
 *
 * Creates Google Meet sessions using the Meet REST API v2 (persistent spaces).
 * Spaces created this way are "open" — anyone with the link can join immediately
 * without waiting for a host to admit them.
 *
 * Required environment variables:
 *   GOOGLE_CLIENT_ID       — OAuth 2.0 Client ID
 *   GOOGLE_CLIENT_SECRET   — OAuth 2.0 Client Secret
 *   GOOGLE_REFRESH_TOKEN   — Refresh token (must include meetings.space.created scope)
 *
 * Required OAuth scopes (regenerate refresh token if adding for first time):
 *   https://www.googleapis.com/auth/meetings.space.created
 */
class GoogleMeetService
{
    private const TOKEN_URL      = 'https://oauth2.googleapis.com/token';
    private const MEET_SPACES    = 'https://meet.googleapis.com/v2/spaces';
    private const MEET_CONF_REC  = 'https://meet.googleapis.com/v2/conferenceRecords';

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Create a persistent Meet space with open access (no host required).
     *
     * Returns an array on success:
     *   [
     *     'uri'       => 'https://meet.google.com/abc-defg-hij',
     *     'spaceName' => 'spaces/jQCqqt3LDn5x',   // store this for admin lookups
     *     'code'      => 'abc-defg-hij',
     *   ]
     *
     * Returns null on any failure (session can still be created without a link).
     */
    public static function createMeetSpace(): ?array
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $payload = [
            'config' => [
                'accessType'       => 'OPEN',  // anyone with link joins instantly
                'entryPointAccess' => 'ALL',
            ],
        ];

        $ch = curl_init(self::MEET_SPACES);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300 || !$body) {
            error_log('[GoogleMeetService] createMeetSpace failed. HTTP ' . $code . ' — ' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['meetingUri'])) {
            error_log('[GoogleMeetService] createMeetSpace unexpected response: ' . $body);
            return null;
        }

        return [
            'uri'       => $data['meetingUri'],
            'spaceName' => $data['name']        ?? null,
            'code'      => $data['meetingCode'] ?? null,
        ];
    }

    /**
     * Fetch current space details for admin oversight.
     *
     * @param  string $spaceName  e.g. "spaces/jQCqqt3LDn5x"
     * @return array|null
     */
    public static function getSpaceDetails(string $spaceName): ?array
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $url = 'https://meet.googleapis.com/v2/' . ltrim($spaceName, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) {
            error_log('[GoogleMeetService] getSpaceDetails failed. HTTP ' . $code . ' — ' . $body);
            return null;
        }

        return json_decode($body, true) ?: null;
    }

    /**
     * Fetch conference records for a space (past and active meetings).
     * Ordered most-recent first.
     *
     * @param  string $spaceName  e.g. "spaces/jQCqqt3LDn5x"
     * @return array  List of conference record objects (may be empty)
     */
    public static function getConferenceRecords(string $spaceName): array
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return [];
        }

        $filter = urlencode('space.name="' . $spaceName . '"');
        $url    = self::MEET_CONF_REC . '?filter=' . $filter . '&orderBy=startTime+desc&pageSize=10';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) {
            error_log('[GoogleMeetService] getConferenceRecords failed. HTTP ' . $code . ' — ' . $body);
            return [];
        }

        $data = json_decode($body, true);
        return $data['conferenceRecords'] ?? [];
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Exchange the refresh token for a short-lived access token.
     */
    private static function getAccessToken(): ?string
    {
        $clientId     = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $refreshToken = env('GOOGLE_REFRESH_TOKEN');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            error_log('[GoogleMeetService] Missing OAuth credentials.');
            return null;
        }

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) {
            error_log('[GoogleMeetService] Token exchange failed. HTTP ' . $code . ' — ' . $body);
            return null;
        }

        $data = json_decode($body, true);
        return $data['access_token'] ?? null;
    }
}
