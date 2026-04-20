<?php



define('ROOT', dirname(__DIR__));
define('APP_BASE', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// Bootstrap — load env + core helpers
require_once ROOT . '/config/env.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Encryption.php';
require_once ROOT . '/core/Response.php';
require_once ROOT . '/core/Request.php';
require_once ROOT . '/core/Pagination.php';


$path     = Request::path();                    // e.g. "/admin/dashboard"
$path     = '/' . trim($path, '/');            // normalise leading slash
$pagePath = ROOT . '/pages' . $path . '/index.php';


if (str_contains($path, '..')) {
    Response::abort(400, 'Bad Request');
}


if ($path === '/') {
    require_once ROOT . '/pages/landing/index.php';
    exit;
}

// Serve the page if it exists
if (file_exists($pagePath)) {
    require_once $pagePath;
} else {
    // 404 — page not found
    http_response_code(404);
    require_once ROOT . '/pages/404.php';
}
