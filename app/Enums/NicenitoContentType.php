<?php

namespace App\Enums;

enum NicenitoContentType: string
{
    case Weekly = 'weekly';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Fixed => 'Fijo',
        };
    }

    /**
     * @return array<string,string> value => label
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
