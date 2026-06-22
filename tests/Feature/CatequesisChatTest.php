<?php

namespace Tests\Feature;

use Tests\TestCase;

class CatequesisChatTest extends TestCase
{
    public function test_chatbot_page_is_accessible(): void
    {
        $response = $this->get('/chatbot-catequesis');

        $response->assertOk();
        $response->assertSee('Preg&uacute;ntale a Nicenito', false);
    }

    public function test_chat_endpoint_returns_answer_and_sources(): void
    {
        $response = $this->postJson('/api/catequesis/chat', [
            'message' => 'Tengo miedo y quiero rezar',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'answer',
                'sources' => [
                    '*' => ['type', 'reference'],
                ],
            ]);
    }

    public function test_chat_endpoint_validates_message_length(): void
    {
        $response = $this->postJson('/api/catequesis/chat', [
            'message' => str_repeat('a', 501),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }
}
