@extends('admin.layout')

@section('title', 'Quiz · NicenoBot')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Intentos de quiz</h1>
    <p class="mt-1 text-sm text-slate-500">Lo que respondieron los participantes cuando alcanzaron el límite de preguntas.</p>

    <form method="GET" class="mt-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3 lg:grid-cols-4">
        <select name="participant_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los participantes</option>
            @foreach ($participants as $p)
                <option value="{{ $p->id }}" @selected(($filters['participant_id'] ?? '') == $p->id)>{{ $p->full_name }}</option>
            @endforeach
        </select>
        <select name="group" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los grupos</option>
            @foreach ($groups as $group)
                <option value="{{ $group }}" @selected(($filters['group'] ?? '') === $group)>{{ $group }}</option>
            @endforeach
        </select>
        <select name="nicenito_content_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Cualquier semanal</option>
            @foreach ($weeklyContents as $w)
                <option value="{{ $w->id }}" @selected(($filters['nicenito_content_id'] ?? '') == $w->id)>{{ $w->title }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <div class="sm:col-span-3 lg:col-span-4">
            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrar</button>
            <a href="{{ route('admin.nicenito.quiz.index') }}" class="ml-2 text-sm text-slate-500 hover:underline">Limpiar</a>
        </div>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Participante</th>
                    <th class="px-4 py-3">Grupo</th>
                    <th class="px-4 py-3">Contenido</th>
                    <th class="px-4 py-3">Resultado</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $attempt)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $attempt->participant?->display_name ?? $attempt->participant?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $attempt->participant?->group_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $attempt->content?->title ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $attempt->score }} / {{ $attempt->total }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.nicenito.quiz.show', $attempt) }}" class="text-slate-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Aún no hay intentos de quiz.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
@endsection
