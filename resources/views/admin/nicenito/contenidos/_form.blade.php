@php
    use App\Enums\NicenoBotContentType;

    $isEdit = $mode === 'edit';
    $action = $isEdit
        ? route('admin.nicenito.contenidos.update', $content)
        : route('admin.nicenito.contenidos.store');

    $lines = fn (?array $items) => collect($items ?? [])->implode("\n");
    $faqLines = collect($content->faq ?? [])
        ->map(fn ($row) => ($row['question'] ?? '').' :: '.($row['answer'] ?? ''))
        ->implode("\n");
    $quizLines = collect($content->quiz_questions ?? [])
        ->map(fn ($row) => ($row['question'] ?? '')
            .' :: '.collect($row['options'] ?? [])->implode(' | ')
            .' :: '.($row['correct'] ?? 0))
        ->implode("\n");
    $dt = fn ($value) => $value?->format('Y-m-d\TH:i');
    $typeValue = $content->type?->value ?? NicenoBotContentType::Weekly->value;
@endphp

<div class="p-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color: var(--admin-text);">{{ $isEdit ? 'Editar contenido' : 'Nuevo contenido' }}</h2>
        <button type="button" data-modal-close aria-label="Cerrar" class="text-xl leading-none" style="color: var(--admin-text-soft);">&times;</button>
    </div>

    <div data-form-errors class="mt-4 hidden rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"></div>

    <form data-ajax-form method="POST" action="{{ $action }}" class="mt-4 space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div>
                    <label class="admin-label">Título</label>
                    <input name="title" value="{{ $content->title }}" required class="admin-input mt-1">
                </div>
                <div>
                    <label class="admin-label">Slug</label>
                    <input name="slug" value="{{ $content->slug }}" placeholder="Se genera del título si lo dejas vacío" class="admin-input mt-1">
                </div>
                <div>
                    <label class="admin-label">Resumen</label>
                    <textarea name="summary" rows="3" required class="admin-input mt-1">{{ $content->summary }}</textarea>
                </div>
                <div>
                    <label class="admin-label">Contenido</label>
                    <textarea name="content" rows="8" required class="admin-input mt-1">{{ $content->content }}</textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label">Referencias bíblicas <span class="font-normal" style="color: var(--admin-text-soft);">(una por línea)</span></label>
                        <textarea name="biblical_references_text" rows="2" placeholder="Juan 1, 1-14" class="admin-input mt-1">{{ $lines($content->biblical_references) }}</textarea>
                    </div>
                    <div>
                        <label class="admin-label">Referencias del Catecismo</label>
                        <textarea name="catechism_references_text" rows="2" placeholder="CEC 464-469" class="admin-input mt-1">{{ $lines($content->catechism_references) }}</textarea>
                    </div>
                    <div>
                        <label class="admin-label">Ideas clave</label>
                        <textarea name="key_ideas_text" rows="2" class="admin-input mt-1">{{ $lines($content->key_ideas) }}</textarea>
                    </div>
                    <div>
                        <label class="admin-label">Preguntas de reflexión</label>
                        <textarea name="reflection_questions_text" rows="2" class="admin-input mt-1">{{ $lines($content->reflection_questions) }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="admin-label">Preguntas frecuentes <span class="font-normal" style="color: var(--admin-text-soft);">(pregunta :: respuesta)</span></label>
                    <textarea name="faq_text" rows="3" placeholder="¿Quién es Jesús? :: Es el Hijo de Dios hecho hombre." class="admin-input mt-1">{{ $faqLines }}</textarea>
                </div>
                <div>
                    <label class="admin-label">Etiquetas <span class="font-normal" style="color: var(--admin-text-soft);">(coma o línea)</span></label>
                    <textarea name="tags_text" rows="2" placeholder="trinidad, jesus, hijo de dios" class="admin-input mt-1">{{ $lines($content->tags) }}</textarea>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="admin-label">Tipo</label>
                    <select name="type" id="type-select" class="admin-input mt-1">
                        @foreach (NicenoBotContentType::options() as $value => $label)
                            <option value="{{ $value }}" @selected($typeValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div data-type-block="fixed">
                    <label class="admin-label">Categoría</label>
                    <select name="category" class="admin-input mt-1">
                        <option value="">— Selecciona —</option>
                        @foreach (config('nicenito.categories') as $category)
                            <option value="{{ $category }}" @selected($content->category === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div data-type-block="weekly" class="space-y-4">
                    <div>
                        <label class="admin-label">Referencia del Evangelio</label>
                        <input name="gospel_reference" value="{{ $content->gospel_reference }}" placeholder="Mateo 10, 26-33" class="admin-input mt-1">
                    </div>
                    <div>
                        <label class="admin-label">Inicio</label>
                        <input type="datetime-local" name="starts_at" value="{{ $dt($content->starts_at) }}" class="admin-input mt-1">
                    </div>
                    <div>
                        <label class="admin-label">Término</label>
                        <input type="datetime-local" name="ends_at" value="{{ $dt($content->ends_at) }}" class="admin-input mt-1">
                    </div>
                    <p class="text-xs" style="color: var(--admin-text-soft);">Zona horaria {{ config('nicenito.timezone') }}.</p>

                    <div>
                        <label class="admin-label">Quiz <span class="font-normal" style="color: var(--admin-text-soft);">(hasta 4; pregunta :: opción A | opción B | opción C :: índice correcto)</span></label>
                        <textarea name="quiz_questions_text" rows="5" placeholder="¿Qué actitud propone Jesús frente al miedo en este pasaje? :: Confiar en Dios | Resignarse | Ignorar el problema :: 0" class="admin-input mt-1">{{ $quizLines }}</textarea>
                        <p class="mt-1 text-xs" style="color: var(--admin-text-soft);">Una pregunta por línea. El índice empieza en 0 (0 = primera opción). Aparece cuando el joven alcanza el límite de preguntas.</p>
                    </div>
                </div>

                @if ($isEdit)
                    <a href="{{ route('admin.nicenito.contenidos.preview', $content) }}" target="_blank" class="btn-secondary w-full">Vista previa con NicenoBot</a>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t pt-4" style="border-color: var(--admin-card-border);">
            <button type="button" data-modal-close class="btn-secondary">Cancelar</button>
            <button type="submit" name="action" value="draft" class="btn-secondary">Guardar borrador</button>
            <button type="submit" name="action" value="publish" class="btn-primary">Publicar</button>
        </div>
    </form>
</div>
