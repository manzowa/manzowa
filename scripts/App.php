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

class App
{
    private static ?\PDO $pdo = null;
   
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
     * Méthode pour récupérer toutes les images
     * @return array
     */
    public static function getImages(): array
    {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM images ORDER BY id ASC");
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
     * Methode pour récupérer les images par type
     *
     * @return array
     */
    public static function getImagesByType(string $type): array
    {
        try {
            $pdo = static::getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, type FROM images WHERE type = :type ORDER BY id ASC"
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
    public static function updateImageType(int $id, string $type): void 
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare(" UPDATE images SET type = :type WHERE id = :id");
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            echo "✅ Image mise à jour : $id\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la mise à jour de l'image : " . $e->getMessage() . "\n";
        }
    }
}