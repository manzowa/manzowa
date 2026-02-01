<?php
namespace App\Helper;

enum UserStatus: string
{
    case ACTIVE    = 'active';
    case INACTIVE  = 'inactive';
    case SUSPENDED = 'suspended';
    case BLOCKED   = 'blocked';

    public static function isValid(string $status): bool
    {
        return in_array($status, array_map(fn($s) => $s->value, self::cases()), true);
    }
    
    public static function all(): array
    {
        return array_map(fn($r) => $r->value, self::cases());
    }   
}