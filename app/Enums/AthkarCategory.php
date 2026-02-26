<?php

namespace App\Enums;

enum AthkarCategory: string
{
    case MORNING = 'صباحي';
    case EVENING = 'مسائي';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function toArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->name] = $case->value;
            return $carry;
        }, []);
    }
}