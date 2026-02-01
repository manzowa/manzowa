<?php
namespace Migrations\Seeds;

interface SeedInterface
{
    public function run(\PDO $pdo): void;
}
