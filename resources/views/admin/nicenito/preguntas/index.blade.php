@extends('admin.layout')

@section('title', 'Preguntas · Nicenito')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Preguntas</h1>

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
        <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todas las categorías</option>
            @foreach (config('nicenito.categories') as $category)
                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="weekly_content_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Cualquier semanal</option>
            @foreach ($weeklyContents as $w)
                <option value="{{ $w->id }}" @selected(($filters['weekly_content_id'] ?? '') == $w->id)>{{ $w->title }}</option>
            @endforeach
        </select>
        <select name="follow_up_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Cualquier seguimiento</option>
            @foreach (App\Enums\FollowUpStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['follow_up_status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="needs_human" value="1" @checked(! empty($filters['needs_human'])) class="rounded border-slate-300"> Requiere acompañamiento</label>
        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="no_content" value="1" @checked(! empty($filters['no_content'])) class="rounded border-slate-300"> Sin contenido suficiente</label>
        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="no_answer" value="1" @checked(! empty($filters['no_answer'])) class="rounded border-slate-300"> Sin respuesta</label>
        <div class="sm:col-span-3 lg:col-span-4">
            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrar</button>
            <a href="{{ route('admin.nicenito.preguntas.index') }}" class="ml-2 text-sm text-slate-500 hover:underline">Limpiar</a>
        </div>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Participante</th>
                    <th class="px-4 py-3">Grupo</th>
                    <th class="px-4 py-3">Pregunta</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3">Semanal</th>
                    <th class="px-4 py-3">Gemini</th>
                    <th class="px-4 py-3">Seguimiento</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $question)
                    <tr class="{{ $question->needs_human_guidance ? 'bg-rose-50/40' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $question->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $question->participant?->display_name ?? $question->participant?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $question->participant?->group_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ Str::limit($question->question, 60) ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $question->detected_category ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $question->weeklyContent?->title ? '✓' : '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $question->used_gemini ? 'Sí' : 'No' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $question->follow_up_status->badgeClasses() }}">
                                {{ $question->follow_up_status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.nicenito.preguntas.show', $question) }}" class="text-slate-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-slate-400">No hay preguntas que coincidan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>
@endsection
