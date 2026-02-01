<?php

namespace Migrations;

/**
 * File Db.php
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *  
 * @category Migrations
 * @package  Migrations    
 * @author   Christian SHUNGU
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

class Db
{
    // L'unique instance du singleton
    private static ?Db $instance = null;

    // La connexion PDO
    private ?\PDO $pdo = null;

    // Constructeur privé pour empêcher l'instanciation
    private function __construct()
    {
        try {
            $this->pdo = new \PDO(
                getenv('DATABASE_SECURITY_DNS') ?? '',
                getenv('DATABASE_SECURITY_USER') ?? '',
                getenv('DATABASE_SECURITY_PASSWORD') ?? ''
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "❌  Error: " . $e->getMessage();
        }
    }

    // Empêche le clonage
    private function __clone() {}

    // Empêche la désérialisation
    public function __wakeup() {}

    /**
     * Méthode pour obtenir l'unique instance de Db
     *
     * @return Db
     */
    public static function getInstance(): Db
    {
        if (self::$instance === null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    /**
     * Méthode pour obtenir la connexion PDO
     *
     * @return \PDO|null
     */
    public function getConnection(): ?\PDO
    {
        return $this->pdo;
    }

    /**
     * Méthode pour fermer la connexion PDO
     *
     * @return void
     */
    public function closeConnection(): void
    {
        $this->pdo = null;
    }
}
