<?php
define('DS', DIRECTORY_SEPARATOR);
define('__ROOT__', dirname(__DIR__));
require_once join(DS, [__ROOT__, 'vendor', 'autoload.php']);
require_once join(DS, [__ROOT__, 'scripts', 'AppRun.php']);
try {
    \Dotenv\Dotenv::createUnsafeImmutable(__ROOT__)->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    $msg = "❌ Warning: .env file not found at " . __ROOT__ . '/.env';
    echo $msg . PHP_EOL;
    error_log($msg);
    die();
}
AppRun::setUp();