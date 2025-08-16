<?php

/**
 * /auth/logout — clears JWT cookie and redirects to login.
 * POST only.
 */
Auth::clearTokenCookie();
Response::redirect('/auth/login');
