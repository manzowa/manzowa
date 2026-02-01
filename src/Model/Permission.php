<?php 

/**
 * Permission Model
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

final class Permission 
{
    public int $id;
    public string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
    public static function fromState(array $data = []): Permission
    {
        return new Permission(
            $data['id'],
            $data['name']
        );
    }
    public static function fromObject(object $data): Permission
    {
        return new Permission(
            $data->id,
            $data->name
        );
    }

    public static function fromJson(string $json): Permission {
        $data = json_decode($json, true);
        return self::fromState($data);
    }
}