@extends('admin.layout')

@php
    use App\Enums\NicenitoContentStatus;
    use App\Enums\NicenitoContentType;

    $isEdit = $mode === 'edit';
    $action = $isEdit
        ? route('admin.nicenito.contenidos.update', $content)
        : route('admin.nicenito.contenidos.store');

    // Convierte un arreglo guardado en texto multilínea para los textareas.
    $lines = fn (?array $items) => collect($items ?? [])->implode("\n");
    $faqLines = collect($content->faq ?? [])
        ->map(fn ($row) => ($row['question'] ?? '').' :: '.($row['answer'] ?? ''))
        ->implode("\n");
    $dt = fn ($value) => $value?->format('Y-m-d\TH:i');
    $typeValue = old('type', $content->type?->value ?? NicenitoContentType::Weekly->value);
@endphp

@section('title', $isEdit ? 'Editar contenido' : 'Nuevo contenido')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Editar contenido' : 'Nuevo contenido' }}</h1>
        @if ($isEdit)
            <a href="{{ route('admin.nicenito.contenidos.preview', $content) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Vista previa con Nicenito
            </a>
        @endif
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="mt-6 space-y-6" id="content-form">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Título</label>
                        <input name="title" value="{{ old('title', $content->title) }}" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Slug</label>
                        <input name="slug" value="{{ old('slug', $content->slug) }}" placeholder="Se genera del título si lo dejas vacío"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Resumen</label>
                        <textarea name="summary" rows="3" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('summary', $content->summary) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contenido</label>
                        <textarea name="content" rows="10" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('content', $content->content) }}</textarea>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <p class="text-sm font-semibold text-slate-700">Campos múltiples (uno por línea)</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Referencias bíblicas</label>
                            <textarea name="biblical_references_text" rows="3" placeholder="Juan 1, 1-14" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('biblical_references_text', $lines($content->biblical_references)) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Referencias del Catecismo</label>
                            <textarea name="catechism_references_text" rows="3" placeholder="CEC 464-469" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('catechism_references_text', $lines($content->catechism_references)) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Ideas clave</label>
                            <textarea name="key_ideas_text" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('key_ideas_text', $lines($content->key_ideas)) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preguntas de reflexión</label>
                            <textarea name="reflection_questions_text" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('reflection_questions_text', $lines($content->reflection_questions)) }}</textarea>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Preguntas frecuentes <span class="text-slate-400">(formato: pregunta :: respuesta)</span></label>
                        <textarea name="faq_text" rows="4" placeholder="¿Quién es Jesús? :: Es el Hijo de Dios hecho hombre." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('faq_text', $faqLines) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Etiquetas <span class="text-slate-400">(separadas por coma o línea)</span></label>
                        <textarea name="tags_text" rows="2" placeholder="trinidad, jesus, hijo de dios" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('tags_text', $lines($content->tags)) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo</label>
                        <select name="type" id="type-select" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (NicenitoContentType::options() as $value => $label)
                                <option value="{{ $value }}" @selected($typeValue === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div data-type-block="fixed">
                        <label class="block text-sm font-medium text-slate-700">Categoría</label>
                        <select name="category" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">— Selecciona —</option>
                            @foreach (config('nicenito.categories') as $category)
                                <option value="{{ $category }}" @selected(old('category', $content->category) === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div data-type-block="weekly" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Referencia del Evangelio</label>
                            <input name="gospel_reference" value="{{ old('gospel_reference', $content->gospel_reference) }}" placeholder="Mateo 10, 26-33"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Inicio</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $dt($content->starts_at)) }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Término</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $dt($content->ends_at)) }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <p class="text-xs text-slate-400">Las fechas se evalúan en zona horaria {{ config('nicenito.timezone') }}.</p>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-3">
                    <p class="text-sm font-medium text-slate-700">Estado actual:
                        <span class="font-semibold">{{ $content->status?->label() ?? 'Borrador' }}</span>
                    </p>
                    <button type="submit" name="action" value="draft"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Guardar borrador
                    </button>
                    <button type="submit" name="action" value="publish"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Publicar
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const select = document.getElementById('type-select');
            const toggle = () => {
                const type = select.value;
                document.querySelectorAll('[data-type-block]').forEach((el) => {
                    el.style.display = el.dataset.typeBlock === type ? '' : 'none';
                });
            };
            select.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endsection
