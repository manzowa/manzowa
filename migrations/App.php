<?php 

namespace Migrations;
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

class App
{

    private ?\PDO $pdo;

    public function __construct() {
        $this->pdo = Db::getInstance()->getConnection();
        $this->ensureMigrationsTableExists();
    }
   
    public function versions()
    {
        $versions = [];
        // Dossier migrations
        $versionFiles = glob(__ROOT__.DS.join(DS, ['migrations', 'versions', 'Version_*.php']));
        if (!$versionFiles) {
            $message = "❌ Aucun fichier de migration trouvé.";
            $this->logMessage($message);
            return;
        }
        if (!is_array($versionFiles)) {
            $message = "❌ Erreur lors de la lecture des fichiers de migration.";
            $this->logMessage($message);
            return;
        }
        // Trier les fichiers par ordre alphabétique
        sort($versionFiles);
        foreach ($versionFiles as $file) {
            $version = pathinfo($file, PATHINFO_FILENAME);
            $className = 'Migrations\\Versions\\' . $version;
            if (class_exists($className)) {
                $versions[$version] = $className;
            } 
        }
        return $versions;
    }
    
    public function migrate(): void
    {
        $versions = $this->versions();
        if (!$versions && !is_array($versions)) {
            $message = "❌ Aucune migration à exécuter.";
            $this->logMessage($message);
            return;
        }
        foreach ($versions as $version => $class) {
            if (!$this->isMigrated($version)) {
                echo "🚀 Migration $version...\n";
                $migration = new $class();
                $migration->up($this->pdo);
                $this->logMigration($version);
                $message = "✅ Migration $version terminée.";
                $this->logMessage($message);
            
            }
        }
        echo "🎉 Toutes les migrations sont à jour.\n";
    }

    private function isMigrated(string $version): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
        return $stmt->fetchColumn() > 0;
    }

    private function logMigration(string $version): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (version, executed_at) VALUES (?, NOW())");
        $stmt->execute([$version]);
    }

    public function logMessage(string $message): void
    {
        echo $message . PHP_EOL;
        error_log($message);
    }

    public function seeders(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            echo "🌱 opération seeds: " . get_class($seeder) . "...\n";
            $seeder->run($this->pdo);
        }
    }

    public function isMigrationsTableExists(): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = 'school_databases' AND table_name = 'migrations'"
        );
        $stmt->execute();
    
        return $stmt->fetchColumn() > 0;
    }

    public function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(191) UNIQUE,
                executed_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $this->pdo->exec($sql);
        $this->logMessage("✅ Table 'migrations' créée avec succès.");
    }

    public function ensureMigrationsTableExists(): void
    {
        if (!$this->isMigrationsTableExists()) {
            $this->createMigrationsTable();
        }
    }

}