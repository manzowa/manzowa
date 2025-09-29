<?php 

/**
 * File Run.php
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *  
 * @category Scripts
 * @package  Scripts    
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
use Gumlet\ImageResize;

class AppRun
{
     /**
     * Méthode pour initialiser et exécuter les processus
     *
     * @return void
     */
    public static function setUp(): void
    {
        // Configuration ou initialisation si nécessaire
        // self::_processResizeImagesInDirectory(
        //     join(DS, [__ROOT__, 'public','uploads']), 
        //     800, 600, ['jpg', 'jpeg', 'png', 'gif', 'webp']
        // );
        // self::_processCreateTableSchedule();
        // sleep(2);
        // self::_processInsertAllScheduleBySchool();
        // self::_processUpdateMaximageOfSchool();
        // self::_processUpdateTypeOfSchool();

        //sleep(2);
        // self::_processCreateTableEvenements();
        // self::_processAlterTableImages();

        // self::_processUpdateTypeOfImage();
        self::_processNoAction();
    }
    /**
     * Méthode pour rédimensionner une image
     *
     * @param string $dir
     * @param int $width
     * @param int $height
     * @param array $allowedExtensions
     * @return void
     */
    private static function _processResizeImagesInDirectory(
        string $dir, int $width, int $height, 
        array $allowedExtensions
    ): void {
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                static::_processResizeImagesInDirectory(
                    $path, $width, $height, $allowedExtensions
                ); // récursion
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
                        
                    } catch (Exception $e) {
                        echo "❌ Erreur avec $path : " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
    /**
     * Méthode pour créer la table horaires
     *
     * @return void
     */
    private static function _processCreateTableSchedule(): void {
        $pdo = App::getConnection();

        try {
            // Supprimer la table si elle existe
            $pdo->exec("DROP TABLE IF EXISTS `horaires`;");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `horaires` (
                    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Schedule',
                    `jour` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Titre Horaire',
                    `debut` time DEFAULT NULL COMMENT 'Debut Horaire',
                    `fin` time DEFAULT NULL COMMENT 'Fin hoaraire',
                    `ecoleid` bigint UNSIGNED NOT NULL COMMENT 'ID school',
                    PRIMARY KEY (`id`),
                    KEY `horaires_to_ecoles_fk` (`ecoleid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Schedule Table';
            ");
            // Ajouter la contrainte de clé étrangère
            $pdo->exec("
                ALTER TABLE `horaires`
                ADD CONSTRAINT `horaires_to_ecole_fk` FOREIGN KEY (`ecoleid`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE;
            ");
            echo "✅ Table 'horaires' créée avec succès.\n";

        } catch (PDOException $e) {
            echo "❌ Erreur lors de la création de la table 'horaires' : " . $e->getMessage() . "\n";
        }
        App::closeConnection();
    }
    /**
     * Méthode pour insérer la table events
     * 
     * @return void
     */
    private static function _processCreateTableEvenements(): void {
        $pdo = App::getConnection();

        try {
            $pdo->exec("DROP TABLE IF EXISTS `evenements`;");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `evenements` (
                    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Event',
                    `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Titre Event',
                    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Description Event',
                    `date` datetime DEFAULT NULL COMMENT 'Datetime Event',
                    `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Lieu Event',
                    `ecoleid` bigint UNSIGNED NOT NULL COMMENT 'ID school',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Events Table';
            ");
            // Ajouter la contrainte de clé étrangère
            $pdo->exec("ALTER TABLE `evenements`
                ADD CONSTRAINT `evenements_to_ecoles_fk` 
                FOREIGN KEY (`ecoleid`) 
                REFERENCES `ecoles` (`id`) ON DELETE CASCADE;
            ");
            $pdo->exec("ALTER TABLE `evenements` 
                ADD `maximage` TINYINT NULL COMMENT 'Image Max' AFTER `ecoleid`;"
            );
            
            echo "✅ Table 'evenements' créée avec succès.\n";

        } catch (PDOException $e) {
            echo "❌ Erreur lors de la création de la table 'evenements' : " . $e->getMessage() . "\n";
        }
        App::closeConnection();

    }
    /**
     * Méthode pour insérer la table horaire
     */
    private static function _processInsertAllScheduleBySchool(): void {
        $schools = App::getSchools();
        $semaines = [
            'Lundi', 'Mardi', 'Mercredi', 'Jeudi',
            'Vendredi', 'Samedi', 'Dimanche'
        ];

        foreach ($schools as $school) {
            $scholid = (int) $school['id'];
            foreach ($semaines as $jour) {
                // Insérer des horaires par défaut pour chaque école
                App::insertSchedule($jour, '00:00:00', '00:00:00', $scholid);
            }
            sleep(1); // Petite pause pour éviter de surcharger la base de données
        }
        echo "✅ Fin insertion horaire\n";
    }

    /**
     * Méthode pour alter la table events
     */
    private static function _processAlterTableImages(): void {
        $pdo = App::getConnection();
        try {
            $pdo->exec(
                "ALTER TABLE `images` ADD `type` 
                CHAR(3) NULL COMMENT 'Type Image' AFTER `mimetype`;"
            );
            $pdo->exec(
                "ALTER TABLE `images` ADD `evenementid` 
                BIGINT UNSIGNED NULL COMMENT 'Event ID' AFTER `ecoleid`;"
            );
            $pdo->exec("
                ALTER TABLE `images`
                ADD CONSTRAINT `images_to_evenements_fk` 
                FOREIGN KEY (`evenementid`) 
                REFERENCES `evenements` (`id`) 
                ON DELETE CASCADE;
            ");
            echo "✅ Table 'images' modifiée avec succès.\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la modification de la table 'images' : " . $e->getMessage() . "\n";
        }
        App::closeConnection();
    }

    /**
     *  Méthode mise à jour du champ maximage
     */
    private static function _processUpdateMaximageOfSchool(): void 
    {
        $images = App::getMaxImagesBySchools();
        foreach ($images as $image) {
            $ecoleid = (int) $image['ecoleid'];
            $maximage = (int) $image['maximage'];
            App::updateSchool($ecoleid, $maximage);
        }
        echo "✅ Fin mise à jour maximage images\n";
    }

    /**
     *  Méthode mise à jour du champ type
     */
    private static function _processUpdateTypeOfSchool(): void 
    {
        $schools = App::getSchoolsByType('prive');

        foreach ($schools as $school) {
            $ecoleid = (int) $school['id'];
            App::updateSchoolType($ecoleid, 'privée');
        }
        echo "✅ Fin mise à jour : type \n";

    }

    /**
     * Méthode mise à jour du champ type
     */
    private static function _processUpdateTypeOfImage(): void
    {
        $images = App::getImages();
        foreach ($images as $image) {
            $imageid = (int) $image['id'];
            App::updateImageType($imageid, 'S');
        }
        echo "✅ Fin mise à jour : type \n";
    }

    /**
     *  Méthode Pas d'action
     */
    private static function _processNoAction(): void 
    {
        echo "✅ Fin d'action \n";
    }
}