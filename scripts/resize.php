<?php
define('DS', DIRECTORY_SEPARATOR);
define('SCRIPT_ROOT', dirname(__DIR__));
require_once join(DS, [SCRIPT_ROOT, 'vendor', 'autoload.php']);

use Gumlet\ImageResize;

$baseDir = join(DS, [SCRIPT_ROOT, 'public','uploads']); // Point de départ
$width = 800;   // Largeur maximale
$height = 600;  // Hauteur maximale

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Fonction récursive pour parcourir les dossiers
function processDirectory($dir, $width, $height, $allowedExtensions) {
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            processDirectory($path, $width, $height, $allowedExtensions); // récursion
        } elseif (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExtensions)) {
                try {
                    $image = new ImageResize($path);
                    $image->crop(
                        $width, $height, true, 
                        ImageResize::CROPCENTER
                    ); // dimensions exactes finales
                    $image->save($path); // on écrase l'image originale
                    echo "✅ Redimensionnée : $path\n";
                } catch (Exception $e) {
                    echo "❌ Erreur avec $path : " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

// Lancer le traitement
processDirectory($baseDir, $width, $height, $allowedExtensions);