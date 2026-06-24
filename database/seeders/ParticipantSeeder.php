<?php

namespace Database\Seeders;

use App\Enums\FollowUpStatus;
use App\Models\NicenitoQuestion;
use App\Models\Participant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        // Participante demo. NO usar estas credenciales en producción.
        $participant = Participant::query()->updateOrCreate(
            ['access_code' => 'NCE-DEMO'],
            [
                'full_name' => 'Martín Pérez',
                'display_name' => 'Martín P.',
                'group_name' => 'Confirmación 2026',
                'pin_hash' => Hash::make('123456'),
                'is_active' => true,
                'must_change_pin' => false,
                'last_login_at' => now(),
                'privacy_notice_accepted_at' => now(),
            ],
        );

        $samples = [
            [
                'question' => '¿Por qué Jesús es Dios si es el Hijo?',
                'detected_category' => 'Jesús y Trinidad',
                'fixed_contents_count' => 1,
            ],
            [
                'question' => '¿Cómo puedo rezar cuando estoy distraído?',
                'detected_category' => 'Oración',
                'fixed_contents_count' => 0,
            ],
        ];

        foreach ($samples as $sample) {
            NicenitoQuestion::query()->updateOrCreate(
                ['participant_id' => $participant->id, 'question' => $sample['question']],
                [
                    'answer' => 'Respuesta de ejemplo generada para pruebas.',
                    'sources' => [],
                    'detected_category' => $sample['detected_category'],
                    'used_gemini' => true,
                    'has_weekly_content' => false,
                    'fixed_contents_count' => $sample['fixed_contents_count'],
                    'needs_human_guidance' => false,
                    'follow_up_status' => FollowUpStatus::None,
                    'answered_at' => now(),
                ],
            );
        }
    }
}
