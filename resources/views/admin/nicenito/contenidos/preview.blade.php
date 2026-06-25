@extends('admin.layout')

@section('title', 'Vista previa · NicenoBot')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Vista previa con NicenoBot</h1>
        <a href="{{ route('admin.nicenito.contenidos.edit', $content) }}" class="text-sm text-slate-600 hover:underline">← Volver a editar</a>
    </div>
    <p class="mt-1 text-sm text-slate-500">
        Contenido base: <span class="font-medium text-slate-700">{{ $content->title }}</span>.
        Esta prueba no afecta conversaciones reales ni guarda la pregunta.
    </p>

    <form method="POST" action="{{ route('admin.nicenito.contenidos.preview', $content) }}" class="mt-6 flex gap-2">
        @csrf
        <input type="text" name="question" value="{{ $question }}" placeholder="Escribe una pregunta de prueba…"
            class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button class="btn-primary">Probar</button>
    </form>

    @if ($result !== null)
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-700">Respuesta generada</h2>
                    @if ($result['ok'] && trim($result['answer']) !== '')
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $result['answer'] }}</p>
                    @else
                        <p class="mt-2 text-sm text-rose-700">No se obtuvo respuesta (status {{ $result['status'] }}). Revisa la API key o los límites.</p>
                    @endif
                    @if ($result['ok'])
                        <p class="mt-3 text-xs text-slate-400">
                            Tokens: {{ $result['usage']['total_tokens'] ?? '—' }} ·
                            finish: {{ $result['finish_reason'] ?? '—' }}
                        </p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-700">Fuentes (entregadas por el backend)</h2>
                    @forelse ($context['sources'] as $source)
                        <p class="mt-2 text-sm text-slate-700">
                            <span class="font-medium">{{ $source['type'] }}:</span> {{ $source['reference'] }}
                            <span class="text-slate-400">— {{ $source['title'] }}</span>
                        </p>
                    @empty
                        <p class="mt-2 text-sm text-slate-400">Sin fuentes.</p>
                    @endforelse
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-700">Contenido recuperado</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Semanal activo:
                        <span class="font-medium">{{ $context['weekly_content']->title ?? 'ninguno' }}</span>
                    </p>
                    <p class="text-sm text-slate-600">
                        Fijos:
                        @if ($context['fixed_contents']->isEmpty()) ninguno
                        @else {{ $context['fixed_contents']->pluck('title')->implode(', ') }}
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Confianza estimada: {{ $context['confidence'] }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-700">Prompt del sistema</h2>
                    <pre class="mt-2 whitespace-pre-wrap text-xs text-slate-600">{{ $systemPrompt }}</pre>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold text-slate-700">Contexto enviado a Gemini</h2>
                    <pre class="mt-2 whitespace-pre-wrap text-xs text-slate-600">{{ $userPrompt }}</pre>
                </div>
            </div>
        </div>
    @endif
@endsection
