<?php

namespace App\Http\Requests;

use App\Enums\NicenoBotContentStatus;
use App\Enums\NicenoBotContentType;
use App\Models\NicenoBotContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NicenoBotContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización del panel la cubre el middleware nicenito.admin.
        return true;
    }

    /**
     * Convierte los campos de texto multilínea del formulario en arreglos,
     * para que el administrador nunca tenga que escribir JSON a mano.
     */
    protected function prepareForValidation(): void
    {
        // Los botones "Guardar borrador" / "Publicar" llegan como action.
        $action = $this->input('action');
        if ($action === 'publish') {
            $this->merge(['status' => NicenoBotContentStatus::Published->value]);
        } elseif ($action === 'draft') {
            $this->merge(['status' => NicenoBotContentStatus::Draft->value]);
        }

        $this->merge([
            'biblical_references' => $this->linesToArray($this->input('biblical_references_text')),
            'catechism_references' => $this->linesToArray($this->input('catechism_references_text')),
            'key_ideas' => $this->linesToArray($this->input('key_ideas_text')),
            'reflection_questions' => $this->linesToArray($this->input('reflection_questions_text')),
            'tags' => $this->linesToArray($this->input('tags_text'), ',', "\n"),
            'faq' => $this->faqToArray($this->input('faq_text')),
            'quiz_questions' => $this->quizToArray($this->input('quiz_questions_text')),
        ]);

        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('content')?->id;

        return [
            'type' => ['required', Rule::enum(NicenoBotContentType::class)],
            'status' => ['required', Rule::enum(NicenoBotContentStatus::class)],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', Rule::unique('nicenito_contents', 'slug')->ignore($id)],
            'summary' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],

            'category' => [
                'nullable',
                'string',
                Rule::in(config('nicenito.categories')),
                Rule::requiredIf(fn () => $this->input('type') === NicenoBotContentType::Fixed->value),
            ],

            'gospel_reference' => [
                'nullable',
                'string',
                'max:180',
                Rule::requiredIf(fn () => $this->input('type') === NicenoBotContentType::Weekly->value),
            ],

            'starts_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('type') === NicenoBotContentType::Weekly->value),
            ],
            'ends_at' => [
                'nullable',
                'date',
                'after:starts_at',
                Rule::requiredIf(fn () => $this->input('type') === NicenoBotContentType::Weekly->value),
            ],

            'biblical_references' => ['array'],
            'biblical_references.*' => ['string', 'max:180'],
            'catechism_references' => ['array'],
            'catechism_references.*' => ['string', 'max:180'],
            'key_ideas' => ['array', 'max:12'],
            'key_ideas.*' => ['string', 'max:280'],
            'reflection_questions' => ['array'],
            'reflection_questions.*' => ['string', 'max:280'],
            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:60'],
            'faq' => ['array'],
            'faq.*.question' => ['required', 'string', 'max:280'],
            'faq.*.answer' => ['required', 'string', 'max:600'],

            // Quiz: hasta 4 preguntas de opción múltiple (2 a 4 alternativas).
            'quiz_questions' => ['array', 'max:4'],
            'quiz_questions.*.question' => ['required', 'string', 'max:280'],
            'quiz_questions.*.options' => ['required', 'array', 'min:2', 'max:4'],
            'quiz_questions.*.options.*' => ['required', 'string', 'max:160'],
            'quiz_questions.*.correct' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // El índice de la alternativa correcta debe existir entre las opciones.
            foreach ((array) $this->input('quiz_questions', []) as $i => $question) {
                $options = $question['options'] ?? [];
                $correct = $question['correct'] ?? null;
                if (is_array($options) && is_int($correct) && $correct >= count($options)) {
                    $validator->errors()->add(
                        "quiz_questions.{$i}.correct",
                        'La alternativa correcta no coincide con ninguna opción de la pregunta '.($i + 1).'.',
                    );
                }
            }

            if (! $this->isPublishedWeekly()) {
                return;
            }

            if ($this->overlapsWithPublishedWeekly()) {
                $validator->errors()->add(
                    'starts_at',
                    'Ya existe un contenido semanal publicado en ese rango de fechas. Ajusta las fechas o archiva el otro.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'La fecha de término debe ser posterior a la de inicio.',
            'category.required' => 'El contenido fijo necesita una categoría.',
            'gospel_reference.required' => 'El contenido semanal necesita la referencia del Evangelio.',
            'starts_at.required' => 'El contenido semanal necesita una fecha de inicio.',
            'ends_at.required' => 'El contenido semanal necesita una fecha de término.',
        ];
    }

    private function isPublishedWeekly(): bool
    {
        return $this->input('type') === NicenoBotContentType::Weekly->value
            && $this->input('status') === NicenoBotContentStatus::Published->value
            && filled($this->input('starts_at'))
            && filled($this->input('ends_at'));
    }

    private function overlapsWithPublishedWeekly(): bool
    {
        return NicenoBotContent::hasPublishedWeeklyOverlap(
            Carbon::parse($this->input('starts_at')),
            Carbon::parse($this->input('ends_at')),
            $this->route('content')?->id,
        );
    }

    /**
     * @return array<int,string>
     */
    private function linesToArray(?string $value, string ...$separators): array
    {
        if (blank($value)) {
            return [];
        }

        $separators = $separators ?: ["\n"];
        $normalized = str_replace($separators, "\n", $value);

        return collect(explode("\n", $normalized))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Cada línea con formato "pregunta :: respuesta".
     *
     * @return array<int,array{question:string,answer:string}>
     */
    private function faqToArray(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(explode("\n", $value))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->map(function (string $line) {
                $parts = explode('::', $line, 2);

                return [
                    'question' => trim($parts[0] ?? ''),
                    'answer' => trim($parts[1] ?? ''),
                ];
            })
            ->filter(fn (array $row) => $row['question'] !== '' && $row['answer'] !== '')
            ->values()
            ->all();
    }

    /**
     * Cada línea con formato "pregunta :: opciónA | opciónB | opciónC :: índiceCorrecto".
     * El índice es base 0 (0 = primera opción). Si se omite, se asume 0.
     *
     * @return array<int,array{question:string,options:array<int,string>,correct:int}>
     */
    private function quizToArray(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(explode("\n", $value))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->map(function (string $line) {
                $parts = array_map('trim', explode('::', $line));

                $options = collect(explode('|', $parts[1] ?? ''))
                    ->map(fn (string $option) => trim($option))
                    ->filter()
                    ->values()
                    ->all();

                $correct = isset($parts[2]) && is_numeric($parts[2]) ? (int) $parts[2] : 0;

                return [
                    'question' => $parts[0] ?? '',
                    'options' => $options,
                    'correct' => $correct,
                ];
            })
            ->filter(fn (array $row) => $row['question'] !== '' && count($row['options']) >= 2)
            ->values()
            ->all();
    }
}
