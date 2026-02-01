<?php 

namespace App\Helper;

final class Metadata
{
    public function __construct(
        private string $key,
        private string $value
    ) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'value' => $this->value,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['key'], $data['value']);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
  