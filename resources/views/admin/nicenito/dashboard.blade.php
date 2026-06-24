@extends('admin.layout')

@section('title', 'Dashboard · Nicenito')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.nicenito.contenidos.create', ['type' => 'weekly']) }}"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Crear contenido semanal
            </a>
            <a href="{{ route('admin.nicenito.contenidos.create', ['type' => 'fixed']) }}"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Crear contenido fijo
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Contenidos fijos publicados</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $fixedPublished }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Borradores pendientes</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $drafts }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 sm:col-span-2">
            <p class="text-sm font-medium text-slate-500">Semanal activo</p>
            @if ($activeWeekly)
                <a href="{{ route('admin.nicenito.contenidos.edit', $activeWeekly) }}" class="mt-1 block text-lg font-semibold text-emerald-700 hover:underline">
                    {{ $activeWeekly->title }}
                </a>
                <p class="text-sm text-slate-500">
                    {{ $activeWeekly->gospel_reference }} ·
                    {{ $activeWeekly->starts_at?->format('d/m/Y') }} – {{ $activeWeekly->ends_at?->format('d/m/Y') }}
                </p>
            @else
                <p class="mt-2 text-sm text-slate-400">No hay contenido semanal vigente en este momento.</p>
            @endif
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-sm font-medium text-slate-500">Próximo contenido semanal programado</p>
        @if ($nextWeekly)
            <a href="{{ route('admin.nicenito.contenidos.edit', $nextWeekly) }}" class="mt-1 block text-lg font-semibold text-slate-900 hover:underline">
                {{ $nextWeekly->title }}
            </a>
            <p class="text-sm text-slate-500">Inicia el {{ $nextWeekly->starts_at?->format('d/m/Y') }}</p>
        @else
            <p class="mt-2 text-sm text-slate-400">No hay semanas futuras programadas.</p>
        @endif
    </div>
@endsection
