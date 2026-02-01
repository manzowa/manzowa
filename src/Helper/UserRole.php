<?php

namespace App\Helper;

enum UserRole: int
{
    case ADMIN     = 1;
    case PREMIUM   = 2;
    case STANDARD  = 3;

    public static function isValid(string $status): bool
    {
        return in_array($status, array_map(fn($s) => $s->value, self::cases()), true);
    }
    
    public static function all(): array
    {
        return array_map(fn($r) => $r->value, self::cases());
    }   
    
     /**
     * Return a string representation of the role.
     *
     * @return string
     */
    public function getName(): string
    {
        $roleNames = [
            self::ADMIN => 'admin',
            self::PREMIUM => 'premium',
            self::STANDARD => 'standard',
        ];

        return $roleNames[$this->value];
    }
}