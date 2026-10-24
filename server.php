<?php
declare(strict_types=1);

// Local PHP built-in-server router. Apache hosting uses .htaccess instead.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (preg_match('#^/(?:app|storage)(?:/|$)#i', $path) || $path === '/server.php') {
    http_response_code(404);
    exit('Not found');
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // Let PHP's built-in server serve static assets.
}

require __DIR__ . '/index.php';
