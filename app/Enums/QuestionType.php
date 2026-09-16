<?php

namespace App\Enums;

enum QuestionType: string
{
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
    case STATEMENT = 'statement';

    /**
     * Human-readable Indonesian label for UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Pilihan Ganda',
            self::MULTIPLE => 'Pilihan Ganda Kompleks',
            self::STATEMENT => 'Sesuai / Tidak Sesuai',
        };
    }

    /**
     * All valid string values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
