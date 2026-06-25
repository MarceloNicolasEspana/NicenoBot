@extends('participant.layout')

@section('title', 'Ingresar · NicenoBot')
@section('heading', 'Ingresa para conversar')

@section('content')
    <p class="text-sm leading-6 text-[var(--niceno-muted)]">
        Usa el código y el PIN que te entregó tu catequista.
    </p>

    <form method="POST" action="{{ route('participant.access.login') }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <label for="access_code" class="block text-sm font-semibold text-[var(--niceno-ink)]">Código de acceso</label>
            <input id="access_code" name="access_code" value="{{ old('access_code') }}" required autofocus
                placeholder="NCE-XXXX" autocomplete="off"
                class="mt-1 w-full rounded-xl border border-[color:var(--niceno-border)] bg-white px-4 py-3 text-sm uppercase tracking-widest outline-none focus:border-[color:var(--niceno-gold)] focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
        </div>
        <div>
            <label for="pin" class="block text-sm font-semibold text-[var(--niceno-ink)]">PIN</label>
            <input id="pin" name="pin" type="password" inputmode="numeric" required
                placeholder="6 dígitos" autocomplete="off"
                class="mt-1 w-full rounded-xl border border-[color:var(--niceno-border)] bg-white px-4 py-3 text-sm tracking-widest outline-none focus:border-[color:var(--niceno-gold)] focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
        </div>
        <button type="submit"
            class="w-full rounded-2xl bg-[var(--niceno-burgundy)] px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
            Entrar
        </button>
    </form>

    <p class="mt-5 text-xs leading-5 text-[var(--niceno-muted)]">
        ¿Perdiste tu código o PIN? Pídele ayuda a tu catequista.
    </p>
@endsection
