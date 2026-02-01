<?php 

/**
 * RolePermission Model
 * 
 * php version 8.2
 *
 * @category App\Model
 * @package  App\Model
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App\Model;

final class RolePermission 
{
    public int $role_id;
    public int $permission_id;

    public function __construct(int $role_id, int $permission_id)
    {
        $this->role_id = $role_id;
        $this->permission_id = $permission_id;
    }

    public function toArray(): array
    {
        return [
            'role_id' => $this->role_id,
            'permission_id' => $this->permission_id,
        ];
    }
    public static function fromState(array $data = []): RolePermission
    {
        return new RolePermission(
            $data['role_id'],
            $data['permission_id']
        );
    }

    public static function fromJson(string $json): RolePermission {
        $data = json_decode($json, true);
        return self::fromState($data);
    }
}