@extends('participant.layout')

@section('title', 'Cambiar PIN · NicenoBot')
@section('heading', 'Crea tu PIN personal')

@section('content')
    <p class="text-sm leading-6" style="color: var(--lp-text-soft);">
        Por seguridad, elige un PIN de 6 dígitos que solo tú conozcas. Tu código de acceso no cambia.
    </p>

    <form method="POST" action="{{ route('participant.pin.update') }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <label for="pin" class="admin-label">Nuevo PIN</label>
            <input id="pin" name="pin" type="password" inputmode="numeric" required maxlength="6"
                placeholder="6 dígitos" autocomplete="off" class="admin-input mt-1 tracking-widest">
        </div>
        <div>
            <label for="pin_confirmation" class="admin-label">Repite el PIN</label>
            <input id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" required maxlength="6"
                placeholder="6 dígitos" autocomplete="off" class="admin-input mt-1 tracking-widest">
        </div>
        <button type="submit" class="btn-primary w-full">Guardar PIN</button>
    </form>
@endsection
