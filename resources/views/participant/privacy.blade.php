@extends('participant.layout')

@section('title', 'Antes de empezar · NicenoBot')
@section('heading', 'Antes de empezar')

@section('content')
    <p class="text-sm leading-7 text-[var(--niceno-ink)]">
        Tus preguntas se registran para que el equipo de catequesis pueda comprender mejor los temas
        del grupo y acompañarte cuando sea necesario. No escribas direcciones, teléfonos, contraseñas
        u otros datos privados.
    </p>

    <form method="POST" action="{{ route('participant.privacy.accept') }}" class="mt-6 space-y-5">
        @csrf
        <label class="flex items-start gap-3 text-sm text-[var(--niceno-ink)]">
            <input type="checkbox" name="accept" value="1" required class="mt-0.5 h-5 w-5 rounded border-[color:var(--niceno-border)]">
            <span>Entiendo</span>
        </label>
        <button type="submit"
            class="w-full rounded-2xl bg-[var(--niceno-burgundy)] px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
            Continuar al chat
        </button>
    </form>
@endsection
