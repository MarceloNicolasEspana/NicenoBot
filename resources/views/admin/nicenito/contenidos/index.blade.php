@extends('admin.layout')

@section('title', 'Contenidos · NicenoBot')

@php
    use App\Enums\NicenoBotContentStatus;
    use App\Enums\NicenoBotContentType;
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Contenidos</h1>
        <button type="button" class="btn-primary" data-modal-url="{{ route('admin.nicenito.contenidos.create') }}">
            Nuevo contenido
        </button>
    </div>

    <form method="GET" class="mt-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-5">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por título"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
        <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los tipos</option>
            @foreach (NicenoBotContentType::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach (NicenoBotContentStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todas las categorías</option>
            @foreach (config('nicenito.categories') as $category)
                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <div class="sm:col-span-5">
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Filtrar
            </button>
        </div>
    </form>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Fechas</th>
                    <th class="px-4 py-3">Actualizado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($contents as $content)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $content->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $content->type->label() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $content->category ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $content->status->badgeClasses() }}">
                                {{ $content->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            @if ($content->type === NicenoBotContentType::Weekly)
                                {{ $content->starts_at?->format('d/m/Y') }} – {{ $content->ends_at?->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $content->updated_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.nicenito.contenidos.preview', $content) }}" class="text-slate-600 hover:underline">Ver</a>
                                <button type="button" class="text-slate-600 hover:underline" data-modal-url="{{ route('admin.nicenito.contenidos.edit', $content) }}">Editar</button>
                                @if ($content->status !== NicenoBotContentStatus::Published)
                                    <form method="POST" action="{{ route('admin.nicenito.contenidos.publish', $content) }}">
                                        @csrf
                                        <button class="text-emerald-700 hover:underline">Publicar</button>
                                    </form>
                                @endif
                                @if ($content->status !== NicenoBotContentStatus::Archived)
                                    <form method="POST" action="{{ route('admin.nicenito.contenidos.archive', $content) }}">
                                        @csrf
                                        <button class="text-slate-600 hover:underline">Archivar</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.nicenito.contenidos.duplicate', $content) }}">
                                    @csrf
                                    <button class="text-slate-600 hover:underline">Duplicar</button>
                                </form>
                                <form method="POST" action="{{ route('admin.nicenito.contenidos.destroy', $content) }}"
                                    onsubmit="return confirm('¿Eliminar este contenido?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">No hay contenidos que coincidan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $contents->links() }}
    </div>
@endsection
