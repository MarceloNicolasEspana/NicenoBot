@extends('admin.layout')

@section('title', 'Editar perfil · NicenoBot')

@php
    $initials = collect(explode(' ', trim($user->name ?? $user->email)))
        ->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp

@section('content')
    {{-- Cabecera --}}
    <div class="flex items-center gap-4">
        <span class="admin-avatar !h-14 !w-14 text-lg" aria-hidden="true">{{ $initials }}</span>
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--admin-text);">Editar perfil</h1>
            <p class="text-sm" style="color: var(--admin-text-soft);">{{ $user->email }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.nicenito.perfil.update') }}" class="mt-6 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        {{-- Datos de la cuenta --}}
        <div class="admin-card space-y-4">
            <h2 class="text-base font-bold" style="color: var(--admin-text);">Datos de la cuenta</h2>
            <div>
                <label class="admin-label" for="name">Nombre</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="admin-input mt-1">
            </div>
            <div>
                <label class="admin-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="admin-input mt-1">
            </div>
        </div>

        {{-- Cambiar contraseña --}}
        <div class="admin-card space-y-4">
            <h2 class="text-base font-bold" style="color: var(--admin-text);">Cambiar contraseña</h2>
            <p class="text-xs" style="color: var(--admin-text-soft);">Déjalo en blanco si no quieres cambiarla.</p>
            <div>
                <label class="admin-label" for="current_password">Contraseña actual</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="admin-input mt-1">
            </div>
            <div>
                <label class="admin-label" for="password">Nueva contraseña</label>
                <input id="password" name="password" type="password" autocomplete="new-password" class="admin-input mt-1">
            </div>
            <div>
                <label class="admin-label" for="password_confirmation">Confirmar nueva contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="admin-input mt-1">
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.nicenito.dashboard') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
