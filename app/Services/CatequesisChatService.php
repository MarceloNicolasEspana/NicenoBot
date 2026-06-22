<?php

namespace App\Services;

use Illuminate\Support\Str;

class CatequesisChatService
{
    /**
     * @return array{answer:string,sources:array<int,array{type:string,reference:string}>}
     */
    public function respond(string $message): array
    {
        $normalizedMessage = $this->normalize($message);

        $topics = [
            [
                'keywords' => ['miedo', 'temor', 'angustia', 'ansiedad', 'preocupacion', 'asustado'],
                'answer' => 'Sentir miedo no te aleja de Dios. Muchas veces el miedo aparece cuando no sabemos que viene, pero Jesus repite que no tengamos miedo porque el Padre nos cuida. Puedes hablarle con sinceridad, pedir ayuda y no cargar solo con lo que te preocupa. Si el miedo es muy fuerte o constante, tambien conviene buscar acompanamiento de un adulto de confianza o un profesional. Pregunta para reflexionar: Que miedo te gustaria poner hoy en manos de Dios?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Mateo 10, 26-33'],
                ],
            ],
            [
                'keywords' => ['gracias', 'agradecido', 'agradecida', 'bendecido', 'bendecida', 'gratitud'],
                'answer' => 'Me alegra que te sientas agradecido. La gratitud es una forma hermosa de reconocer las bendiciones en nuestra vida y fortalecer nuestra relacion con Dios. Puedes expresarla en la oracion, compartiendo tu alegria con otros o tomando un momento para reconocer lo bueno que has recibido. Pregunta para reflexionar: Por que cosas estas agradecido hoy?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => '1 Tesalonicenses 5, 16-18'],
                ],
            ],
            [
                'keywords' => ['hola', 'saludos', 'buenos dias', 'buenas tardes', 'buenas noches'],
                'answer' => 'Hola. Me alegra que hayas venido. En que puedo ayudarte hoy?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Lucas 24, 15'],
                ],
            ],
            [
                'keywords' => ['quien eres', 'que eres', 'quien es nicenito', 'que es nicenito', 'nicenito'],
                'answer' => 'Soy un asistente para catequesis de la fe cristiana catolica. Mi nombre recuerda el Concilio de Nicea: Nicea, Niceno, Nicenito. Estoy aqui para ayudarte a entender mejor la fe, responder preguntas sencillas y acompanarte en tu camino de crecimiento espiritual. No reemplazo a tu catequista, sacerdote o adulto responsable, pero puedo ayudarte a preparar mejor tus preguntas.',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => '1 Pedro 3, 15'],
                ],
            ],
            [
                'keywords' => ['oracion', 'orar', 'rezar', 'rezo', 'rezo mejor'],
                'answer' => 'Rezar mejor no significa usar palabras complicadas. Puedes empezar con unos minutos de silencio, hablarle a Dios como hablas con alguien que te ama y terminar dando gracias. Tambien ayuda leer un pequeno trozo del Evangelio y preguntarte que te dice Jesus hoy. Pregunta para reflexionar: Que momento del dia podrias regalarle a Dios esta semana?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Mateo 6, 5-13'],
                ],
            ],
            [
                'keywords' => ['confesion', 'confesar', 'reconciliacion', 'pecados al sacerdote'],
                'answer' => 'La confesion es el sacramento en el que Dios nos ofrece perdon y un nuevo comienzo. No es para humillarte, sino para sanar el corazon y volver a caminar con paz. Prepararte con sinceridad, reconocer tus faltas y confiar en la misericordia de Dios hace mucho bien. Si tienes dudas concretas, un sacerdote o catequista puede orientarte paso a paso. Pregunta para reflexionar: Hay algo que necesitas entregar a Dios para empezar de nuevo?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Juan 20, 21-23'],
                ],
            ],
            [
                'keywords' => ['fe', 'confiar en dios', 'creer', 'dudas de fe'],
                'answer' => 'Tener fe es confiar en Dios incluso cuando no entiendes todo. La fe no elimina las preguntas; mas bien te anima a seguir buscando con el corazon abierto. Crece con la oracion, la comunidad, los sacramentos y el esfuerzo por vivir como Jesus. Pregunta para reflexionar: En que parte de tu vida te cuesta mas confiar en Dios hoy?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Hebreos 11, 1'],
                ],
            ],
            [
                'keywords' => ['evangelio', 'domingo', 'palabra de dios', 'lecturas'],
                'answer' => 'El Evangelio nos muestra quien es Jesus, como ama y como nos invita a vivir. Cuando escuchas el Evangelio del domingo, puedes fijarte en una frase que te toque y llevarla contigo durante la semana. Si quieres, tambien puedes leerlo antes de misa y pensar: que me ensena Jesus, que me corrige y que me consuela. Pregunta para reflexionar: Que palabra de Jesus necesitas escuchar con mas atencion?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Lucas 4, 16-21'],
                ],
            ],
            [
                'keywords' => ['pecado', 'caida', 'culpa', 'mal que hice'],
                'answer' => 'El pecado es aquello que rompe nuestra amistad con Dios, dana a otros o nos hace dano por dentro. Reconocerlo no es vivir culpandonos sin salida, sino abrir espacio para el perdon y el cambio. Dios siempre llama a volver, y por eso la Iglesia nos acompana con la oracion, la confesion y la vida en comunidad. Pregunta para reflexionar: Que paso concreto podrias dar hoy para reparar un dano o acercarte mas a Dios?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Lucas 15, 11-24'],
                ],
            ],
            [
                'keywords' => ['sacramento', 'sacramentos', 'bautismo', 'eucaristia', 'comunion', 'confirmacion'],
                'answer' => 'Los sacramentos son signos visibles del amor y la gracia de Dios en momentos importantes de la vida cristiana. No son solo ritos bonitos: son encuentros reales con Cristo que fortalecen la fe y la comunidad. Conocerlos mejor ayuda a vivirlos con mas sentido y alegria. Pregunta para reflexionar: Que sacramento te gustaria comprender o vivir con mas profundidad?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Mateo 28, 19-20'],
                ],
            ],
            [
                'keywords' => ['jesus', 'jesuscristo', 'cristo', 'senor'],
                'answer' => 'Jesus es el Hijo de Dios que se hizo cercano para mostrarnos el amor del Padre. En el vemos como vivir con verdad, compasion, valentia y entrega. Conocer a Jesus no es solo aprender datos sobre el, sino dejar que su forma de amar transforme tu vida. Pregunta para reflexionar: Que rasgo de Jesus te gustaria imitar esta semana?',
                'sources' => [
                    ['type' => 'Biblia', 'reference' => 'Juan 14, 6-9'],
                ],
            ],
        ];

        foreach ($topics as $topic) {
            foreach ($topic['keywords'] as $keyword) {
                if (str_contains($normalizedMessage, $this->normalize($keyword))) {
                    return [
                        'answer' => $topic['answer'],
                        'sources' => $topic['sources'],
                    ];
                }
            }
        }

        return [
            'answer' => 'Todavia estoy aprendiendo sobre este tema. Puedes reformular tu pregunta o conversarla con tu catequista, sacerdote o un adulto de confianza.',
            'sources' => [],
        ];
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
            ->squish()
            ->value();
    }
}
