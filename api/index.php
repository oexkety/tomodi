<?php

// Front controller Laravel 13 untuk runtime PHP komunitas pada Vercel.
// routes/api.php tetap merupakan tempat deklarasi API Tomodi.

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
$root = dirname(__DIR__);

// Hindari Symfony menganggap /api sebagai base URL karena file entrypoint
// berada di /api/index.php. REQUEST_URI asli tetap dipertahankan.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

$onVercel = getenv('VERCEL') === '1';

if ($onVercel) {
    $temporaryRoot = sys_get_temp_dir() . '/tomodi-' . substr(hash('sha256', $root), 0, 12);
    foreach (['cache', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $directory) {
        $path = $temporaryRoot . '/' . $directory;
        if (! is_dir($path) && ! mkdir($path, 0700, true) && ! is_dir($path)) {
            http_response_code(500);
            exit('Penyimpanan sementara aplikasi tidak tersedia.');
        }
    }

    $overrides = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'LOG_CHANNEL' => 'stderr',
        'APP_PACKAGES_CACHE' => $temporaryRoot . '/cache/packages.php',
        'APP_SERVICES_CACHE' => $temporaryRoot . '/cache/services.php',
        'APP_CONFIG_CACHE' => $temporaryRoot . '/cache/config.php',
        'APP_ROUTES_CACHE' => $temporaryRoot . '/cache/routes.php',
        'APP_EVENTS_CACHE' => $temporaryRoot . '/cache/events.php',
        'VIEW_COMPILED_PATH' => $temporaryRoot . '/storage/framework/views',
    ];
    foreach ($overrides as $name => $value) {
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';

if ($onVercel) {
    $app->useStoragePath($temporaryRoot . '/storage');
    TrustProxies::at('*');
}

$app->handleRequest(Request::capture());
