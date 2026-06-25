@extends('admin.layout')

@section('title', 'Participantes · NicenoBot')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Participantes</h1>
        <button type="button" class="btn-primary" data-modal-url="{{ route('admin.nicenito.participantes.create') }}">
            Crear participante
        </button>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="group" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los grupos</option>
            @foreach ($groups as $group)
                <option value="{{ $group }}" @selected(($filters['group'] ?? '') === $group)>{{ $group }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrar</button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nombre completo</th>
                    <th class="px-4 py-3">Visible</th>
                    <th class="px-4 py-3">Grupo</th>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Último acceso</th>
                    <th class="px-4 py-3">Preguntas</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($participants as $participant)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $participant->full_name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $participant->display_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $participant->group_name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $participant->access_code }}</td>
                        <td class="px-4 py-3">
                            @if ($participant->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-300">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $participant->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            <a href="{{ route('admin.nicenito.preguntas.index', ['participant_id' => $participant->id]) }}" class="hover:underline">
                                {{ $participant->questions_count }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button type="button" class="text-slate-600 hover:underline" data-modal-url="{{ route('admin.nicenito.participantes.edit', $participant) }}">Editar</button>
                                <form method="POST" action="{{ route('admin.nicenito.participantes.toggle', $participant) }}">
                                    @csrf
                                    <button class="text-slate-600 hover:underline">{{ $participant->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.nicenito.participantes.regenerate-pin', $participant) }}">
                                    @csrf
                                    <button class="text-amber-700 hover:underline">PIN</button>
                                </form>
                                <form method="POST" action="{{ route('admin.nicenito.participantes.regenerate-code', $participant) }}">
                                    @csrf
                                    <button class="text-amber-700 hover:underline">Código</button>
                                </form>
                                <form method="POST" action="{{ route('admin.nicenito.participantes.destroy', $participant) }}"
                                    onsubmit="return confirm('¿Eliminar este participante?');">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">No hay participantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $participants->links() }}</div>
@endsection
