<?php

namespace Migrations\Seeds;

class RolesSeeder implements SeedInterface
{
    public function run(\PDO $pdo): void
    {
        $roles = [
            'admin', 'premium', 'standard'
        ];

        $values = [];
        foreach ($roles as $role) {
            $values[] = "('$role')";
        }
        $sql = "INSERT IGNORE INTO roles (name) VALUES " . implode(',', $values);
        $pdo->exec($sql);
    }
}
