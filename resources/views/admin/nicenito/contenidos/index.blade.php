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
                            <div class="flex flex-wrap items-center justify-end gap-1.5">
                                <a href="{{ route('admin.nicenito.contenidos.preview', $content) }}"
                                    class="action-icon action-icon--view" title="Ver" aria-label="Ver">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 4c-4.2 0-7.4 3.3-8.5 5.4a1.3 1.3 0 0 0 0 1.2C2.6 12.7 5.8 16 10 16s7.4-3.3 8.5-5.4a1.3 1.3 0 0 0 0-1.2C17.4 7.3 14.2 4 10 4Zm0 9a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
                                    </svg>
                                </a>
                                <button type="button" class="action-icon action-icon--edit" title="Editar" aria-label="Editar"
                                    data-modal-url="{{ route('admin.nicenito.contenidos.edit', $content) }}">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M13.6 2.9a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 1 0 2.1l-8.6 8.6-3.7.7.7-3.7 8.1-8.1Z"/>
                                    </svg>
                                </button>
                                @if ($content->status !== NicenoBotContentStatus::Published)
                                    <form method="POST" action="{{ route('admin.nicenito.contenidos.publish', $content) }}">
                                        @csrf
                                        <button class="action-icon action-icon--publish" title="Publicar" aria-label="Publicar">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M3 9.5 17 3l-3.5 14-3.4-5.1L3 9.5Zm7.2 1.7 1.6 2.4 1.7-6.8-3.3 4.4Z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                @if ($content->status !== NicenoBotContentStatus::Archived)
                                    <form method="POST" action="{{ route('admin.nicenito.contenidos.archive', $content) }}">
                                        @csrf
                                        <button class="action-icon action-icon--archive" title="Archivar" aria-label="Archivar">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M3 4h14v3H3V4Zm1 4h12v8H4V8Zm3 2v1.5h6V10H7Z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.nicenito.contenidos.duplicate', $content) }}">
                                    @csrf
                                    <button class="action-icon action-icon--duplicate" title="Duplicar" aria-label="Duplicar">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M7 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm-3 5v9a2 2 0 0 0 2 2h7v-2H6V7H4Z"/>
                                        </svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.nicenito.contenidos.destroy', $content) }}"
                                    onsubmit="return confirm('¿Eliminar este contenido?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-icon action-icon--delete" title="Eliminar" aria-label="Eliminar">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M8 2h4l1 1h3v2H4V3h3l1-1ZM5 6h10l-.8 11.1A2 2 0 0 1 12.2 19H7.8a2 2 0 0 1-2-1.9L5 6Z"/>
                                        </svg>
                                    </button>
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
