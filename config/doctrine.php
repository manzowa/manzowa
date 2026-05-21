<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

// require_once __DIR__ . '/../vendor/autoload.php';

// $paths = [__DIR__ . '/../app/Entities'];

$isDevMode = true;

$config = ORMSetup::createAttributeMetadataConfiguration(
    $paths,
    $isDevMode
);

$connection = [
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'slim_db',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

$conn = DriverManager::getConnection(
    $connection,
    $config
);

return new EntityManager($conn, $config);