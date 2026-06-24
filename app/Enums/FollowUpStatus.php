<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case None = 'none';
    case Review = 'review';
    case CatechistFollowUp = 'catechist_follow_up';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sin marcar',
            self::Review => 'Para revisar',
            self::CatechistFollowUp => 'Seguimiento catequista',
            self::Resolved => 'Resuelto',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::None => 'bg-slate-100 text-slate-600 ring-slate-200',
            self::Review => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::CatechistFollowUp => 'bg-rose-100 text-rose-800 ring-rose-200',
            self::Resolved => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
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
