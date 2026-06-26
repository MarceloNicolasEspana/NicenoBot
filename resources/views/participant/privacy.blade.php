@extends('participant.layout')

@section('title', 'Antes de empezar · NicenoBot')
@section('heading', 'Antes de empezar')

@section('content')
    <p class="text-sm leading-7" style="color: var(--lp-text);">
        Tus preguntas se registran para que el equipo de catequesis pueda comprender mejor los temas
        del grupo y acompañarte cuando sea necesario. No escribas direcciones, teléfonos, contraseñas
        u otros datos privados.
    </p>

    <form method="POST" action="{{ route('participant.privacy.accept') }}" class="mt-6 space-y-5">
        @csrf
        <label class="flex items-start gap-3 text-sm" style="color: var(--lp-text);">
            <input type="checkbox" name="accept" value="1" required class="mt-0.5 h-5 w-5 rounded" style="accent-color: var(--lp-primary);">
            <span>Entiendo</span>
        </label>
        <button type="submit" class="btn-primary w-full">Continuar al chat</button>
    </form>
@endsection
