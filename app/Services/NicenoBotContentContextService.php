<?php

namespace App\Services;

use App\Models\NicenoBotContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Recupera el contenido catequético relevante para una pregunta y construye un
 * contexto compacto para Gemini, junto con las fuentes verificadas por el
 * backend (Laravel decide las fuentes, no el modelo).
 *
 * Fase 1: búsqueda léxica por puntaje, sin embeddings ni vectores. La forma del
 * resultado (context_text + sources + confidence) está pensada para que una
 * Fase 2 pueda reemplazar el scorer por búsqueda semántica sin tocar al resto
 * del flujo del chatbot.
 *
 * @phpstan-type Source array{type:string,reference:string,title:string}
 * @phpstan-type ContextResult array{
 *     weekly_content: NicenoBotContent|null,
 *     fixed_contents: Collection<int,NicenoBotContent>,
 *     context_text: string,
 *     sources: array<int,Source>,
 *     confidence: float
 * }
 */
class NicenoBotContentContextService
{
    /**
     * @return ContextResult
     */
    public function build(string $question): array
    {
        $limits = config('nicenito.context');
        $tokens = $this->tokenize($question);
        $normalizedQuestion = $this->normalize($question);

        $weekly = NicenoBotContent::query()->activeWeekly()->first();
        $fixed = $this->relevantFixedContents($tokens, $normalizedQuestion, $limits);

        $sources = $this->buildSources($weekly, $fixed);
        $contextText = $this->buildContextText($weekly, $fixed, $limits);

        return [
            'weekly_content' => $weekly,
            'fixed_contents' => $fixed,
            'context_text' => $contextText,
            'sources' => $sources,
            'confidence' => $this->confidence($weekly, $fixed, $tokens),
        ];
    }

    /**
     * @param  array<int,string>  $tokens
     * @param  array<string,mixed>  $limits
     * @return Collection<int,NicenoBotContent>
     */
    private function relevantFixedContents(array $tokens, string $normalizedQuestion, array $limits): Collection
    {
        if ($tokens === []) {
            return collect();
        }

        return NicenoBotContent::query()
            ->fixed()
            ->published()
            ->get()
            ->map(function (NicenoBotContent $content) use ($tokens, $normalizedQuestion) {
                $content->setAttribute('relevance_score', $this->score($content, $tokens, $normalizedQuestion));

                return $content;
            })
            ->filter(fn (NicenoBotContent $content) => $content->getAttribute('relevance_score') >= $limits['min_relevance_score'])
            ->sortByDesc(fn (NicenoBotContent $content) => $content->getAttribute('relevance_score'))
            ->take($limits['max_fixed_contents'])
            ->values();
    }

    /**
     * Puntaje léxico simple con pesos por campo. Los tokens demasiado genéricos
     * (config nicenito.stopwords) no suman, para evitar arrastrar contenido
     * irrelevante por palabras como "Jesús" o "fe".
     *
     * @param  array<int,string>  $tokens
     */
    private function score(NicenoBotContent $content, array $tokens, string $normalizedQuestion): float
    {
        $fields = [
            'title' => 3.0,
            'tags' => 3.0,
            'faq' => 3.0,
            'category' => 2.5,
            'key_ideas' => 2.0,
            'gospel_reference' => 2.0,
            'summary' => 1.0,
        ];

        $haystacks = [
            'title' => $this->normalize($content->title),
            'tags' => $this->normalize(implode(' ', $content->tags ?? [])),
            'faq' => $this->normalize($this->faqText($content)),
            'category' => $this->normalize((string) $content->category),
            'key_ideas' => $this->normalize(implode(' ', $content->key_ideas ?? [])),
            'gospel_reference' => $this->normalize((string) $content->gospel_reference),
            'summary' => $this->normalize($content->summary),
        ];

        $score = 0.0;

        foreach ($tokens as $token) {
            foreach ($fields as $field => $weight) {
                if ($haystacks[$field] !== '' && str_contains($haystacks[$field], $token)) {
                    $score += $weight;
                }
            }
        }

        // Bonus por frase: si título, tags o FAQ contienen una secuencia de la
        // pregunta, priorizamos frente a coincidencias de palabras sueltas.
        foreach ($this->phrases($tokens) as $phrase) {
            foreach (['title', 'tags', 'faq'] as $field) {
                if ($haystacks[$field] !== '' && str_contains($haystacks[$field], $phrase)) {
                    $score += 2.0;
                }
            }
        }

        return $score;
    }

    /**
     * @param  array<string,mixed>  $limits
     */
    private function buildContextText(?NicenoBotContent $weekly, Collection $fixed, array $limits): string
    {
        $blocks = [];

        if ($weekly instanceof NicenoBotContent) {
            $blocks[] = $this->weeklyBlock($weekly, $limits);
        }

        foreach ($fixed as $content) {
            $blocks[] = $this->fixedBlock($content, $limits);
        }

        if ($blocks === []) {
            return '';
        }

        $text = implode("\n\n---\n\n", $blocks);

        return Str::limit($text, $limits['total_max_chars'], '…');
    }

    /**
     * @param  array<string,mixed>  $limits
     */
    private function weeklyBlock(NicenoBotContent $weekly, array $limits): string
    {
        $lines = ['[CONTENIDO SEMANAL]'];
        $lines[] = 'Título: '.$weekly->title;

        if (filled($weekly->gospel_reference)) {
            $lines[] = 'Evangelio: '.$weekly->gospel_reference;
        }

        $lines[] = 'Resumen: '.$weekly->summary;

        $ideas = array_slice($weekly->key_ideas ?? [], 0, $limits['max_key_ideas']);
        if ($ideas !== []) {
            $lines[] = 'Ideas clave: '.implode('; ', $ideas);
        }

        if (filled($faq = $this->faqText($weekly))) {
            $lines[] = 'Preguntas frecuentes: '.$faq;
        }

        $lines[] = 'Contenido: '.$weekly->content;

        return Str::limit(implode("\n", $lines), $limits['weekly_max_chars'], '…');
    }

    /**
     * @param  array<string,mixed>  $limits
     */
    private function fixedBlock(NicenoBotContent $content, array $limits): string
    {
        $lines = ['[CONTENIDO FIJO]'];
        $lines[] = 'Título: '.$content->title;

        if (filled($content->category)) {
            $lines[] = 'Categoría: '.$content->category;
        }

        $lines[] = 'Resumen: '.$content->summary;
        $lines[] = 'Contenido: '.$content->content;

        return Str::limit(implode("\n", $lines), $limits['fixed_max_chars'], '…');
    }

    /**
     * Fuentes verificadas a partir de lo realmente almacenado.
     *
     * @return array<int,array{type:string,reference:string,title:string}>
     */
    private function buildSources(?NicenoBotContent $weekly, Collection $fixed): array
    {
        $sources = [];

        if ($weekly instanceof NicenoBotContent && filled($weekly->gospel_reference)) {
            $sources[] = [
                'type' => 'Evangelio',
                'reference' => $weekly->gospel_reference,
                'title' => $weekly->title,
            ];
        }

        $contents = $weekly instanceof NicenoBotContent
            ? collect([$weekly])->concat($fixed)
            : $fixed;

        foreach ($contents as $content) {
            foreach ($content->biblical_references ?? [] as $reference) {
                $sources[] = ['type' => 'Biblia', 'reference' => $reference, 'title' => $content->title];
            }

            foreach ($content->catechism_references ?? [] as $reference) {
                $sources[] = ['type' => 'Catecismo', 'reference' => $reference, 'title' => $content->title];
            }
        }

        return collect($sources)
            ->unique(fn (array $s) => $s['type'].'|'.$s['reference'])
            ->values()
            ->all();
    }

    /**
     * @param  array<int,string>  $tokens
     */
    private function confidence(?NicenoBotContent $weekly, Collection $fixed, array $tokens): float
    {
        $confidence = $weekly instanceof NicenoBotContent ? 0.3 : 0.0;

        $topScore = (float) ($fixed->first()?->getAttribute('relevance_score') ?? 0.0);
        // Normalizamos el puntaje contra una referencia razonable de "buena
        // coincidencia" para acotar la confianza a [0, 1].
        $confidence += min(0.7, $topScore / 12.0);

        return round(min(1.0, $confidence), 2);
    }

    private function faqText(NicenoBotContent $content): string
    {
        return collect($content->faq ?? [])
            ->map(fn (array $row) => trim(($row['question'] ?? '').' '.($row['answer'] ?? '')))
            ->filter()
            ->implode(' ');
    }

    /**
     * @return array<int,string>
     */
    private function tokenize(string $value): array
    {
        $stopwords = config('nicenito.stopwords', []);

        return collect(explode(' ', $this->normalize($value)))
            ->filter(fn (string $token) => mb_strlen($token) >= 3)
            ->reject(fn (string $token) => in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Bigramas a partir de los tokens, para el bonus por frase.
     *
     * @param  array<int,string>  $tokens
     * @return array<int,string>
     */
    private function phrases(array $tokens): array
    {
        $phrases = [];

        for ($i = 0, $n = count($tokens) - 1; $i < $n; $i++) {
            $phrases[] = $tokens[$i].' '.$tokens[$i + 1];
        }

        return $phrases;
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
