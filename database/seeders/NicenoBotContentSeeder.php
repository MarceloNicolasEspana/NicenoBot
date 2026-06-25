<?php

namespace Database\Seeders;

use App\Enums\NicenoBotContentStatus;
use App\Enums\NicenoBotContentType;
use App\Models\NicenoBotContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NicenoBotContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->fixed(
            title: 'Jesús verdadero Dios y verdadero hombre',
            category: 'Jesús y Trinidad',
            summary: 'Jesús es el Hijo de Dios que se hizo hombre: verdadero Dios y verdadero hombre, sin dejar de ser una sola Persona.',
            content: <<<'TXT'
            Jesús es el Hijo eterno de Dios que, por amor, se hizo hombre. Decimos que es
            "verdadero Dios y verdadero hombre": comparte la misma naturaleza divina del Padre
            y, al mismo tiempo, asumió una naturaleza humana como la nuestra, menos el pecado.

            Ser el Hijo no lo hace "menos Dios". En la Trinidad, el Padre, el Hijo y el Espíritu
            Santo son un solo Dios; el Hijo es Dios igual que el Padre, aunque sea una Persona
            distinta. Por eso, que Jesús sea el Hijo y que Jesús sea Dios no se contradice.
            TXT,
            tags: ['jesus', 'hijo de dios', 'encarnacion', 'verdadero dios', 'verdadero hombre', 'divinidad'],
            keyIdeas: [
                'Jesús es el Hijo eterno de Dios hecho hombre.',
                'Es verdadero Dios y verdadero hombre en una sola Persona.',
                'Ser el Hijo no significa ser inferior al Padre.',
            ],
            faq: [
                ['question' => '¿Por qué Jesús es Dios si es el Hijo?', 'answer' => 'Porque el Hijo comparte la misma naturaleza divina del Padre; es Dios igual que Él, aunque sea una Persona distinta dentro de la Trinidad.'],
                ['question' => '¿Jesús es Dios y hombre a la vez?', 'answer' => 'Sí, es verdadero Dios y verdadero hombre, sin mezcla ni separación, en una sola Persona.'],
            ],
            biblical: ['Juan 1, 1-14'],
            catechism: ['CEC 464-469'],
        );

        $this->fixed(
            title: 'La Santísima Trinidad',
            category: 'Jesús y Trinidad',
            summary: 'Un solo Dios en tres Personas: Padre, Hijo y Espíritu Santo, distintas entre sí pero un mismo y único Dios.',
            content: <<<'TXT'
            El misterio central de la fe cristiana es la Santísima Trinidad: creemos en un solo
            Dios que es Padre, Hijo y Espíritu Santo. No son tres dioses, ni tres "modos" del
            mismo; son tres Personas realmente distintas que comparten una única naturaleza divina.

            El Padre no es el Hijo, el Hijo no es el Espíritu, y el Espíritu no es el Padre; sin
            embargo, los tres son el mismo y único Dios, unidos en un amor perfecto. Fuimos
            bautizados precisamente "en el nombre del Padre, y del Hijo, y del Espíritu Santo".
            TXT,
            tags: ['trinidad', 'padre', 'hijo', 'espiritu santo', 'un solo dios', 'tres personas'],
            keyIdeas: [
                'Un solo Dios en tres Personas distintas.',
                'Padre, Hijo y Espíritu Santo comparten una misma naturaleza divina.',
                'Es el misterio central de la fe cristiana.',
            ],
            faq: [
                ['question' => '¿Qué es la Trinidad?', 'answer' => 'Un solo Dios en tres Personas: Padre, Hijo y Espíritu Santo.'],
                ['question' => '¿Son tres dioses?', 'answer' => 'No. Son tres Personas distintas pero un mismo y único Dios.'],
            ],
            biblical: ['Mateo 28, 19'],
            catechism: ['CEC 232-267'],
        );

        // Contenido semanal vigente para pruebas inmediatas en local.
        NicenoBotContent::query()->updateOrCreate(
            ['slug' => 'no-tengan-miedo'],
            [
                'type' => NicenoBotContentType::Weekly,
                'status' => NicenoBotContentStatus::Published,
                'category' => null,
                'title' => 'No tengan miedo',
                'gospel_reference' => 'Mateo 10, 26-33',
                'summary' => 'Jesús nos invita a confiar en Dios y a tener valentía para vivir y mostrar nuestra fe sin miedo.',
                'content' => 'En el Evangelio, Jesús repite "no tengan miedo". Nos recuerda que el Padre cuida de nosotros hasta en los detalles más pequeños, y que podemos confiarle nuestros temores. Vivir la fe con valentía no es no sentir miedo, sino dar pasos confiando en que Dios nos acompaña.',
                'key_ideas' => [
                    'Dios cuida de cada uno de nosotros.',
                    'La confianza vence al miedo.',
                    'Vivir la fe pide valentía, no perfección.',
                ],
                'faq' => [
                    ['question' => '¿Está mal sentir miedo?', 'answer' => 'No. El miedo es humano; Jesús nos invita a confiarlo a Dios y a no quedarnos paralizados por él.'],
                ],
                'reflection_questions' => ['¿Qué miedo te gustaría poner hoy en manos de Dios?'],
                'biblical_references' => ['Mateo 10, 26-33'],
                'catechism_references' => [],
                'tags' => ['miedo', 'confianza', 'valentia', 'temor'],
                'starts_at' => NicenoBotContent::now()->subDays(1)->startOfDay(),
                'ends_at' => NicenoBotContent::now()->addDays(6)->endOfDay(),
            ],
        );
    }

    /**
     * @param  array<int,string>  $tags
     * @param  array<int,string>  $keyIdeas
     * @param  array<int,array{question:string,answer:string}>  $faq
     * @param  array<int,string>  $biblical
     * @param  array<int,string>  $catechism
     */
    private function fixed(
        string $title,
        string $category,
        string $summary,
        string $content,
        array $tags,
        array $keyIdeas,
        array $faq,
        array $biblical,
        array $catechism,
    ): void {
        NicenoBotContent::query()->updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'type' => NicenoBotContentType::Fixed,
                'status' => NicenoBotContentStatus::Published,
                'category' => $category,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'tags' => $tags,
                'key_ideas' => $keyIdeas,
                'faq' => $faq,
                'reflection_questions' => [],
                'biblical_references' => $biblical,
                'catechism_references' => $catechism,
            ],
        );
    }
}
