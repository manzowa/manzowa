<?php
define('DS', DIRECTORY_SEPARATOR);
define('__ROOT__', dirname(__DIR__));
require_once join(DS, [__ROOT__, 'vendor', 'autoload.php']);
require_once join(DS, [__ROOT__, 'scripts', 'AppRun.php']);
\Dotenv\Dotenv::createUnsafeImmutable(__ROOT__)->load();
AppRun::setUp();