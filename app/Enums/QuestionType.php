<?php

namespace App\Enums;

enum QuestionType: string
{
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
    case STATEMENT = 'statement';

public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Pilihan Ganda',
            self::MULTIPLE => 'Pilihan Ganda Kompleks',
            self::STATEMENT => 'Sesuai / Tidak Sesuai',
        };
    }

public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
