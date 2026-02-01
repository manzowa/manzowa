<?php
define('DS', DIRECTORY_SEPARATOR);
define('__ROOT__', dirname(__DIR__));

function autoload_files($dir) {
    $files = glob(__ROOT__.DS.join(DS, ['migrations', $dir, '*.php']));
    // Inclut chaque fichier trouvé
    foreach ($files as $file) {
        if ($file && is_readable($file)) {
            //echo "Chargement du fichier: $file\n";
            require_once $file;
        }
    }
}
require_once join(DS, [__ROOT__, 'vendor', 'autoload.php']);
require_once join(DS, [__ROOT__, 'migrations', 'Db.php']);
require_once join(DS, [__ROOT__, 'migrations', 'App.php']);
require_once join(DS, [__ROOT__, 'migrations', 'seeds', 'SeedInterface.php']);
autoload_files('versions');
autoload_files('seeds');
try {
    \Dotenv\Dotenv::createUnsafeImmutable(__ROOT__)->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    $msg = "❌ Warning: .env file not found at " . __ROOT__ . '/.env';
    echo $msg . PHP_EOL;
    error_log($msg);
    die();
}
$app = new Migrations\App();
$app->migrate();
// Exécuter les seeds
$seeders = [
    new \Migrations\Seeds\RolesSeeder(),
    new \Migrations\Seeds\PermissionsSeeder(),
    new \Migrations\Seeds\RolePermissionsSeeder(),
];

$app->seeders($seeders);
echo "✅ Migration et seeds terminés.\n";