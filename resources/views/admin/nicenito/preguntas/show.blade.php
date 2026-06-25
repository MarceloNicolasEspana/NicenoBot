@extends('admin.layout')

@section('title', 'Detalle de pregunta · NicenoBot')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Detalle de pregunta</h1>
        <a href="{{ route('admin.nicenito.preguntas.index') }}" class="text-sm text-slate-600 hover:underline">← Volver</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-700">Pregunta</h2>
                <p class="mt-2 text-sm text-slate-800">{{ $question->question ?? '(contenido anonimizado)' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-700">Respuesta de NicenoBot</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $question->answer ?? '(contenido anonimizado)' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-700">Fuentes utilizadas</h2>
                @forelse ($question->sources ?? [] as $source)
                    <p class="mt-2 text-sm text-slate-700">
                        <span class="font-medium">{{ $source['type'] ?? '' }}:</span> {{ $source['reference'] ?? '' }}
                        <span class="text-slate-400">— {{ $source['title'] ?? '' }}</span>
                    </p>
                @empty
                    <p class="mt-2 text-sm text-slate-400">Sin fuentes.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm">
                <h2 class="font-semibold text-slate-700">Participante</h2>
                <p class="mt-2 text-slate-800">{{ $question->participant?->full_name ?? '—' }}</p>
                <p class="text-slate-500">{{ $question->participant?->group_name ?? '—' }}</p>
                <hr class="my-3 border-slate-100">
                <p class="text-slate-500">Categoría: <span class="text-slate-700">{{ $question->detected_category ?? '—' }}</span></p>
                <p class="text-slate-500">Semanal: <span class="text-slate-700">{{ $question->weeklyContent?->title ?? '—' }}</span></p>
                <p class="text-slate-500">Contenidos fijos: <span class="text-slate-700">{{ $question->fixed_contents_count }}</span></p>
                <p class="text-slate-500">Gemini: <span class="text-slate-700">{{ $question->used_gemini ? 'Sí' : 'No' }}</span></p>
                <p class="text-slate-500">Requiere acompañamiento: <span class="text-slate-700">{{ $question->needs_human_guidance ? 'Sí' : 'No' }}</span></p>
                <p class="text-slate-500">Fecha: <span class="text-slate-700">{{ $question->created_at->format('d/m/Y H:i') }}</span></p>
            </div>

            <form method="POST" action="{{ route('admin.nicenito.preguntas.follow-up', $question) }}"
                class="rounded-xl border border-slate-200 bg-white p-5">
                @csrf @method('PUT')
                <h2 class="text-sm font-semibold text-slate-700">Seguimiento</h2>
                <select name="follow_up_status" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (App\Enums\FollowUpStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected($question->follow_up_status->value === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="follow_up_notes" rows="4" placeholder="Notas privadas del catequista"
                    class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $question->follow_up_notes }}</textarea>
                @if ($question->followUpBy)
                    <p class="mt-2 text-xs text-slate-400">Última actualización por {{ $question->followUpBy->name }}</p>
                @endif
                <button class="btn-primary mt-3 w-full">Guardar seguimiento</button>
            </form>
        </div>
    </div>
@endsection
