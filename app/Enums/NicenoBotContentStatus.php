<?php

namespace App\Enums;

enum NicenoBotContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Published => 'Publicado',
            self::Archived => 'Archivado',
        };
    }

    /**
     * Clases Tailwind para el badge de estado en el panel.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Published => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::Archived => 'bg-slate-200 text-slate-600 ring-slate-300',
        };
    }

    /**
     * @return array<string,string> value => label
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
