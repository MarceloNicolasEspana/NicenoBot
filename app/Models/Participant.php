<?php

namespace App\Models;

use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property string $full_name
 * @property string|null $display_name
 * @property string|null $group_name
 * @property string $access_code
 * @property bool $is_active
 * @property bool $must_change_pin
 */
class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'display_name',
        'group_name',
        'access_code',
        'pin_hash',
        'is_active',
        'must_change_pin',
        'last_login_at',
        'privacy_notice_accepted_at',
    ];

    /**
     * El hash del PIN nunca debe exponerse en serializaciones.
     *
     * @var list<string>
     */
    protected $hidden = ['pin_hash'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'must_change_pin' => 'boolean',
            'last_login_at' => 'datetime',
            'privacy_notice_accepted_at' => 'datetime',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(NicenoBotQuestion::class);
    }

    // ---------------------------------------------------------------------
    // PIN
    // ---------------------------------------------------------------------

    public function setPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
    }

    public function checkPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    public function hasAcceptedPrivacy(): bool
    {
        return $this->privacy_notice_accepted_at !== null;
    }

    /**
     * Nombre seguro para mostrar dentro del chat (nunca el nombre completo).
     */
    public function safeName(): string
    {
        return $this->display_name ?: 'Tú';
    }

    // ---------------------------------------------------------------------
    // Generadores
    // ---------------------------------------------------------------------

    /**
     * Código aleatorio en mayúsculas, sin datos personales: NCE-XXXX.
     * Evita caracteres ambiguos (0/O, 1/I).
     */
    public static function generateAccessCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = 'NCE-'.$suffix;
        } while (self::query()->where('access_code', $code)->exists());

        return $code;
    }

    public static function generatePin(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
