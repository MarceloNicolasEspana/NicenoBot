<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('keywordProvider')]
    public function test_chat_endpoint_matches_defined_keywords(string $message, string $expectedFragment): void
    {
        $response = $this->postJson('/api/catequesis/chat', [
            'message' => $message,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('answer', fn (string $answer) => str_contains($answer, $expectedFragment));
    }

    public static function keywordProvider(): array
    {
        return [
            ['Gracias por ayudarme', 'agradecido'],
            ['¿Quién eres?', 'Concilio de Nicea'],
            ['Hola Nicenito', 'Me alegra que hayas venido'],
            ['¿Cómo puedo rezar mejor?', 'Rezar mejor'],
            ['¿Qué es la confesión?', 'La confesion'],
            ['¿Qué significa tener fe?', 'Tener fe'],
            ['Explícame el Evangelio del domingo', 'El Evangelio'],
            ['Tengo culpa por un pecado', 'El pecado'],
            ['Que son los sacramentos', 'Los sacramentos'],
            ['Quiero conocer a Jesús', 'Jesus es el Hijo de Dios'],
        ];
    }
}
