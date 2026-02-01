<?php 

namespace Migrations\Versions;


class Version_2026_01_31_CreateMigrationsTable
{
    public static function up(\PDO $pdo): void
    {
        $queries = [
            // CAS général 
            "ALTER TABLE `evenements` DROP FOREIGN KEY `evenements_to_ecoles_fk` ",
            "ALTER TABLE `evenements` MODIFY ecoleid BIGINT UNSIGNED NULL ",
            "ALTER TABLE `evenements` ADD CONSTRAINT `evenements_to_ecoles_fk` FOREIGN KEY (`ecoleid`) REFERENCES `ecoles`",
            // Create tables `roles`
            "CREATE TABLE IF NOT EXISTS `roles` (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            // Create tables `permissions`
            "CREATE TABLE IF NOT EXISTS `permissions` (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            // create table `role_permissions`
            "CREATE TABLE IF NOT EXISTS `role_permissions` (
                role_id INT(11) NOT NULL, permission_id INT(11) NOT NULL, 
                PRIMARY KEY (role_id, permission_id), 
                FOREIGN KEY (role_id) REFERENCES roles(id), 
                FOREIGN KEY (permission_id) REFERENCES permissions(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            // Create table comments
            "CREATE TABLE IF NOT EXISTS comments (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                user_id bigint NOT NULL, 
                school_id bigint UNSIGNED NOT NULL, 
                content TEXT NOT NULL CHECK (LENGTH(content) <= 255), 
                created_at datetime DEFAULT CURRENT_TIMESTAMP, 
                FOREIGN KEY (user_id) REFERENCES users(id), 
                FOREIGN KEY (school_id) REFERENCES ecoles(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ",
            "ALTER TABLE comments ADD CONSTRAINT uniq_user_school_comment UNIQUE (user_id, school_id)",
            // Create table ratings
            "CREATE TABLE IF NOT EXISTS ratings (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                user_id bigint NOT NULL, 
                school_id bigint UNSIGNED NOT NULL, 
                score INT CHECK(score >= 1 AND score <= 5), 
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (school_id) REFERENCES ecoles(id)
            )  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ", 
            "ALTER TABLE ratings ADD CONSTRAINT uniq_user_school UNIQUE (user_id, school_id)",
            "ALTER TABLE `users` 
                CHANGE COLUMN active status ENUM('active', 'inactive', 'suspended', 'blocked') NOT NULL DEFAULT 'inactive',
                ADD COLUMN role_id ENUM('admin', 'premium', 'standard') NOT NULL DEFAULT 'standard' AFTER attempts,
                ADD COLUMN created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER role_id,
                ADD COLUMN updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER created_at,
                ADD COLUMN metadata JSON NULL AFTER created_at,
                ADD FOREIGN KEY (role_id) REFERENCES roles(id)
            ",
        ];
        try 
        {
            foreach ($queries as $query) {
                $pdo->exec($query);
            }
            echo "✅ La table 'users' a été modifiée avec succès.\n";
        } catch (\PDOException $e) {
            echo "❌ Erreur lors de la modification de la table 'users' : " . $e->getMessage() . "\n";
        }
    }
}