<?php

namespace Migrations\Seeds;

class PermissionsSeeder implements SeedInterface
{
    public function run(\PDO $pdo): void
    {
        $permissions = [
            'create_user', 'edit_user', 'delete_user', 'view_user',
            'create_event', 'edit_event', 'delete_event', 'view_event',
            'create_school', 'edit_school', 'delete_school', 'view_school',
            'create_comment', 'edit_comment', 'delete_comment', 'view_comment',
            'create_rating', 'edit_rating', 'delete_rating', 'view_rating',
            'create_image', 'edit_image', 'delete_image', 'view_image'
        ];

        $values = [];
        foreach ($permissions as $perm) {
            $values[] = "('$perm')";
        }
        $sql = "INSERT IGNORE INTO permissions (name) VALUES " . implode(',', $values);
        $pdo->exec($sql);
    }
}
