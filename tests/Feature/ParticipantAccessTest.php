<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParticipantAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_login_with_correct_code_and_pin(): void
    {
        $participant = Participant::factory()->create([
            'access_code' => 'NCE-TEST',
            'pin_hash' => Hash::make('123456'),
        ]);

        $response = $this->post(route('participant.access.login'), [
            'access_code' => 'NCE-TEST',
            'pin' => '123456',
        ]);

        $response->assertRedirect(route('chatbot.show'));
        $response->assertSessionHas('participant_id', $participant->id);
    }

    public function test_wrong_pin_does_not_log_in(): void
    {
        Participant::factory()->create([
            'access_code' => 'NCE-TEST',
            'pin_hash' => Hash::make('123456'),
        ]);

        $response = $this->post(route('participant.access.login'), [
            'access_code' => 'NCE-TEST',
            'pin' => '000000',
        ]);

        $response->assertSessionHasErrors('access_code');
        $response->assertSessionMissing('participant_id');
    }

    public function test_inactive_participant_cannot_access(): void
    {
        Participant::factory()->inactive()->create([
            'access_code' => 'NCE-OFF',
            'pin_hash' => Hash::make('123456'),
        ]);

        $response = $this->post(route('participant.access.login'), [
            'access_code' => 'NCE-OFF',
            'pin' => '123456',
        ]);

        $response->assertSessionHasErrors('access_code');
        $response->assertSessionMissing('participant_id');
    }

    public function test_changing_pin_updates_hash_and_allows_continue(): void
    {
        $participant = Participant::factory()->mustChangePin()->create([
            'pin_hash' => Hash::make('111111'),
        ]);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->post(route('participant.pin.update'), [
                'pin' => '654321',
                'pin_confirmation' => '654321',
            ]);

        $response->assertRedirect(route('chatbot.show'));

        $participant->refresh();
        $this->assertFalse($participant->must_change_pin);
        $this->assertTrue($participant->checkPin('654321'));
    }

    public function test_must_change_pin_blocks_chatbot(): void
    {
        $participant = Participant::factory()->mustChangePin()->create();

        $this->withSession(['participant_id' => $participant->id])
            ->get('/chatbot-catequesis')
            ->assertRedirect(route('participant.pin.show'));
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        Participant::factory()->create([
            'access_code' => 'NCE-TEST',
            'pin_hash' => Hash::make('123456'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('participant.access.login'), ['access_code' => 'NCE-TEST', 'pin' => 'wrong0']);
        }

        $response = $this->post(route('participant.access.login'), ['access_code' => 'NCE-TEST', 'pin' => 'wrong0']);

        $response->assertSessionHasErrors('access_code');
        $this->assertStringContainsString(
            'varios intentos',
            session('errors')->first('access_code'),
        );
    }
}
