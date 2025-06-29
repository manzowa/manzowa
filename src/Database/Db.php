<?php

namespace App\Database;

class Db 
{
    // Holds the instance of the Database
    private static $instance = null;
    // Database connection
    private $connection;

    private function __construct() {
        try {
            $this->connection = new \PDO(
                getenv('DATABASE_SECURITY_DNS')??'', 
                getenv('DATABASE_SECURITY_USER')??'', 
                getenv('DATABASE_SECURITY_PASSWORD')??''
            );

            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $msgError =sprintf("%s sur la ligne ( %s ) : %s", 
                $e->getFile(), $e->getLine(), $e->getMessage()
            );
            error_log($msgError, 0);
        }
    }

    private function __clone() {}

    public function __wakeup() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}