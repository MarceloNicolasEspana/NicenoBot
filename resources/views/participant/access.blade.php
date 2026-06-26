@extends('participant.layout')

@section('title', 'Ingresar · NicenoBot')
@section('heading', 'Ingresa para conversar')

@section('content')
    <p class="text-sm leading-6" style="color: var(--lp-text-soft);">
        Usa el código y el PIN que te entregó tu catequista.
    </p>

    <form method="POST" action="{{ route('participant.access.login') }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <label for="access_code" class="admin-label">Código de acceso</label>
            <input id="access_code" name="access_code" value="{{ old('access_code') }}" required autofocus
                placeholder="NCE-XXXX" autocomplete="off"
                class="admin-input mt-1 uppercase tracking-widest">
        </div>
        <div>
            <label for="pin" class="admin-label">PIN</label>
            <input id="pin" name="pin" type="password" inputmode="numeric" required
                placeholder="6 dígitos" autocomplete="off"
                class="admin-input mt-1 tracking-widest">
        </div>
        <button type="submit" class="btn-primary w-full">Entrar</button>
    </form>

    <p class="mt-5 text-xs leading-5" style="color: var(--lp-text-soft);">
        ¿Perdiste tu código o PIN? Pídele ayuda a tu catequista.
    </p>
@endsection
