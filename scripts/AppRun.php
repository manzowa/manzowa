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
    private static ?\PDO $pdo = null;
    /**
     * Méthode pour initialiser et exécuter les processus
     *
     * @return void
     */
    public static function setUp(): void
    {
        // Configuration ou initialisation si nécessaire
        self::_processResizeImagesInDirectory(
            join(DS, [__ROOT__, 'public','uploads']), 
            800, 600, ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );
        self::_processCreateTableSchedule();
        sleep(2);
        self::_processInsertAllScheduleBySchool();
        self::_processUpdateMaximageOfSchool();
        self::_processUpdateTypeOfSchool();
    }
    /**
     * Méthode pour obtenir la connexion PDO
     *
     * @return \PDO|null
     */
    public static function getConnection(): ?\PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new \PDO(
                    getenv('DATABASE_SECURITY_DNS') ?? '',
                    getenv('DATABASE_SECURITY_USER') ?? '',
                    getenv('DATABASE_SECURITY_PASSWORD') ?? ''
                );
                self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                echo "❌  Error:  " . $e->getMessage();
            }
        }
        return self::$pdo;
    }

    /**
     * Méthode pour fermer la connexion PDO
     *
     * @return void
     */
    public static function closeConnection(): void{
        self::$pdo = null;
    }
    /**
     * Méthode pour récupérer toutes les écoles
     *
     * @return array
     */
    public static function getSchools(): array
    {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM ecoles ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Handle or log the error
            echo "❌Database error: " . $e->getMessage();
            return [];
        }
    }
    /**
     *  Méthode pour récupérer toutes les écoles par type
     *
     * @return array
     */
    public static function getSchoolsByType(string $type): array 
    {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, type FROM ecoles WHERE type = :type ORDER BY id ASC"
            );
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle or log the error
            echo "❌Database error: " . $e->getMessage();
            return [];
        }

    }

    /**
     * Methode 
     *
     * @return array
     */
    public static function getMaxImagesBySchools(): array
    {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare("
                SELECT ecoleid, COUNT(*) AS maximage
                FROM images
                GROUP BY ecoleid
                ORDER BY maximage DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle or log the error
            echo "❌ Database error: " . $e->getMessage();
            return [];
        }
    }
    /**
     * Méthode pour insérer des horaires
     */
    public static function insertSchedule($jour, $debut, $fin, $ecoleid): void {
       
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO horaires (jour, debut, fin, ecoleid) 
                VALUES (:jour, :debut, :fin, :ecoleid)"
            );
            $stmt->bindParam(':jour', $jour);
            $stmt->bindParam(':debut', $debut);
            $stmt->bindParam(':fin', $fin);
            $stmt->bindParam(':ecoleid', $ecoleid);
            $stmt->execute();
            echo "✅ Horaire ajouté : $jour de $debut à $fin\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de l'insertion des horaires : " . $e->getMessage() . "\n";
        } finally {
            self::closeConnection();
        }
    }
     /**
     * Méthode pour mise à jour le champs maximage school 
     * @param int $id
     * @param int $maximage
     */
    public static function updateSchool(int $id, int $maximage): void {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare(" UPDATE ecoles SET maximage = :maximage WHERE id = :id");
            $stmt->bindParam(':maximage', $maximage);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            echo "✅ École mise à jour : $id\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la mise à jour de l'école : " . $e->getMessage() . "\n";
        }
    }
    public static function updateSchoolType(int $id, string $type): void 
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare(" UPDATE ecoles SET type = :type WHERE id = :id");
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            echo "✅ École mise à jour : $id\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la mise à jour de l'école : " . $e->getMessage() . "\n";
        }
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
                        echo "✅ Redimensionnée : $path\n";
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
        $pdo = self::getConnection();

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
        self::closeConnection();
    }
    /**
     * Méthode pour insérer la table horaire
     */
    private static function _processInsertAllScheduleBySchool(): void {
        $schools = self::getSchools();
        $semaines = [
            'Lundi', 'Mardi', 'Mercredi', 'Jeudi',
            'Vendredi', 'Samedi', 'Dimanche'
        ];

        foreach ($schools as $school) {
            $scholid = (int) $school['id'];
            foreach ($semaines as $jour) {
                // Insérer des horaires par défaut pour chaque école
                self::insertSchedule($jour, '00:00:00', '00:00:00', $scholid);
            }
            sleep(1); // Petite pause pour éviter de surcharger la base de données
        }
        echo "✅ Fin insertion horaire";
    }

    /**
     *  Méthode mise à jour du champ maximage
     */
    private static function _processUpdateMaximageOfSchool(): void 
    {
        $images = self::getMaxImagesBySchools();
        foreach ($images as $image) {
            $ecoleid = (int) $image['ecoleid'];
            $maximage = (int) $image['maximage'];
            self::updateSchool($ecoleid, $maximage);
        }
        echo "✅ Fin mise à jour maximage images\n";
    }

    /**
     *  Méthode mise à jour du champ type
     */
    private static function _processUpdateTypeOfSchool(): void 
    {
        $schools = self::getSchoolsByType('prive');

        foreach ($schools as $school) {
            $ecoleid = (int) $school['id'];
            self::updateSchoolType($ecoleid, 'privée');
        }
        echo "✅ Fin mise à jour : type \n";

    }

}