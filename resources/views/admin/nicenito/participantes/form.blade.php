@extends('admin.layout')

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit
        ? route('admin.nicenito.participantes.update', $participant)
        : route('admin.nicenito.participantes.store');
@endphp

@section('title', $isEdit ? 'Editar participante' : 'Nuevo participante')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Editar participante' : 'Nuevo participante' }}</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @unless ($isEdit)
        <p class="mt-2 text-sm text-slate-500">El código de acceso y un PIN temporal se generan automáticamente al guardar.</p>
    @endunless

    <form method="POST" action="{{ $action }}" class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Nombre completo <span class="text-slate-400">(solo visible para el equipo)</span></label>
            <input name="full_name" value="{{ old('full_name', $participant->full_name) }}" required
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Nombre visible <span class="text-slate-400">(ej: Martín P.)</span></label>
            <input name="display_name" value="{{ old('display_name', $participant->display_name) }}"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Grupo</label>
            <input name="group_name" value="{{ old('group_name', $participant->group_name) }}" placeholder="Confirmación 2026"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $participant->is_active)) class="rounded border-slate-300">
            Activo
        </label>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                {{ $isEdit ? 'Guardar cambios' : 'Crear y generar credenciales' }}
            </button>
            <a href="{{ route('admin.nicenito.participantes.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
@endsection
