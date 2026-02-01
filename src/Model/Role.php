<?php 

/**
 * Role Model
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

final class Role {
    private ?int $id;
    private ?string $name;

    public function __construct(?int $id = null, ?string $name = null) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }
    public function setName(?string $name): void {
        $this->name = $name;
    }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
    public static function fromState(array $data = []): Role {

        return new static(
            id: $data['id'] ?? null, 
            name: $data['name'] ?? null
        );
    }
    public static function fromObject(object $data): Role {
        return new static(
            id: $data->id ?? null, 
            name: $data->name ?? null
        );
    }

    public static function fromJson(string $json): Role {
        $data = json_decode($json, true);
        return self::fromState($data);
    }
}