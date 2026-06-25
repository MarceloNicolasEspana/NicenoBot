@extends('participant.layout')

@section('title', 'Cambiar PIN · NicenoBot')
@section('heading', 'Crea tu PIN personal')

@section('content')
    <p class="text-sm leading-6 text-[var(--niceno-muted)]">
        Por seguridad, elige un PIN de 6 dígitos que solo tú conozcas. Tu código de acceso no cambia.
    </p>

    <form method="POST" action="{{ route('participant.pin.update') }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <label for="pin" class="block text-sm font-semibold text-[var(--niceno-ink)]">Nuevo PIN</label>
            <input id="pin" name="pin" type="password" inputmode="numeric" required maxlength="6"
                placeholder="6 dígitos" autocomplete="off"
                class="mt-1 w-full rounded-xl border border-[color:var(--niceno-border)] bg-white px-4 py-3 text-sm tracking-widest outline-none focus:border-[color:var(--niceno-gold)] focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
        </div>
        <div>
            <label for="pin_confirmation" class="block text-sm font-semibold text-[var(--niceno-ink)]">Repite el PIN</label>
            <input id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" required maxlength="6"
                placeholder="6 dígitos" autocomplete="off"
                class="mt-1 w-full rounded-xl border border-[color:var(--niceno-border)] bg-white px-4 py-3 text-sm tracking-widest outline-none focus:border-[color:var(--niceno-gold)] focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
        </div>
        <button type="submit"
            class="w-full rounded-2xl bg-[var(--niceno-burgundy)] px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]">
            Guardar PIN
        </button>
    </form>
@endsection
