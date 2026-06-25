@extends('admin.layout')

@section('title', 'Dashboard · NicenoBot')

@section('content')
    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold" style="color: var(--admin-text);">Dashboard</h1>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-primary"
                data-modal-url="{{ route('admin.nicenito.contenidos.create', ['type' => 'weekly']) }}">
                Crear contenido semanal
            </button>
            <button type="button" class="btn-secondary"
                data-modal-url="{{ route('admin.nicenito.contenidos.create', ['type' => 'fixed']) }}">
                Crear contenido fijo
            </button>
        </div>
    </div>

    {{-- 1. Destacado: semanal activo --}}
    <div class="admin-card-cream mt-6">
        <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--admin-text-soft);">Semanal activo</p>
        @if ($activeWeekly)
            <button type="button" data-modal-url="{{ route('admin.nicenito.contenidos.edit', $activeWeekly) }}"
                class="mt-2 block text-left text-2xl font-bold hover:underline" style="color: var(--admin-active-green);">
                {{ $activeWeekly->title }}
            </button>
            <p class="mt-1 text-sm" style="color: var(--admin-text-soft);">
                {{ $activeWeekly->gospel_reference }}
                · {{ $activeWeekly->starts_at?->format('d/m/Y') }} – {{ $activeWeekly->ends_at?->format('d/m/Y') }}
            </p>
        @else
            <p class="mt-2 text-sm" style="color: var(--admin-text-soft);">No hay contenido semanal vigente en este momento.</p>
        @endif
    </div>

    {{-- 2. Métricas --}}
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="admin-card">
            <p class="text-sm font-medium" style="color: var(--admin-text-soft);">Contenidos fijos publicados</p>
            <p class="mt-2 text-3xl font-bold" style="color: var(--admin-text);">{{ $fixedPublished }}</p>
        </div>
        <div class="admin-card">
            <p class="text-sm font-medium" style="color: var(--admin-text-soft);">Borradores pendientes</p>
            <p class="mt-2 text-3xl font-bold" style="color: var(--admin-text);">{{ $drafts }}</p>
        </div>
    </div>

    {{-- 3. Próximo semanal --}}
    <div class="admin-card mt-4">
        <p class="text-sm font-medium" style="color: var(--admin-text-soft);">Próximo contenido semanal programado</p>
        @if ($nextWeekly)
            <button type="button" data-modal-url="{{ route('admin.nicenito.contenidos.edit', $nextWeekly) }}"
                class="mt-1 block text-left text-lg font-semibold hover:underline" style="color: var(--admin-text);">
                {{ $nextWeekly->title }}
            </button>
            <p class="text-sm" style="color: var(--admin-text-soft);">Inicia el {{ $nextWeekly->starts_at?->format('d/m/Y') }}</p>
        @else
            <p class="mt-2 text-sm" style="color: var(--admin-text-soft);">No hay semanas futuras programadas.</p>
        @endif
    </div>
@endsection
