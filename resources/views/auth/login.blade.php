<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingresar · Panel de NicenoBot</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    <style>body { font-family: ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="flex min-h-full items-center justify-center p-4" style="background: var(--admin-bg);">
    <div class="w-full max-w-sm">
        {{-- Cabecera con el personaje --}}
        <div class="mb-5 flex flex-col items-center text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl shadow-sm" style="background: var(--admin-surface); border: 1px solid var(--admin-border);">
                <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="NicenoBot" class="h-14 w-14 object-contain object-top">
            </div>
            <h1 class="mt-3 text-xl font-bold" style="color: var(--admin-text);">Panel de NicenoBot</h1>
            <p class="mt-1 text-sm" style="color: var(--admin-text-soft);">Ingresa con una cuenta autorizada.</p>
        </div>

        <div class="rounded-2xl p-7 shadow-lg" style="background: var(--admin-card); border: 1px solid var(--admin-card-border);">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="admin-label">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" class="admin-input mt-1">
                </div>
                <div>
                    <label for="password" class="admin-label">Contraseña</label>
                    <input id="password" name="password" type="password" required
                        autocomplete="current-password" class="admin-input mt-1">
                </div>
                <label class="flex items-center gap-2 text-sm" style="color: var(--admin-text-soft);">
                    <input type="checkbox" name="remember" value="1" class="rounded" style="accent-color: var(--admin-primary);">
                    Recordarme
                </label>
                <button type="submit" class="btn-primary w-full">Ingresar</button>
            </form>
        </div>

        <p class="mt-5 text-center text-xs" style="color: var(--admin-text-soft);">
            <a href="{{ url('/') }}" class="hover:underline">← Volver al inicio</a>
        </p>
    </div>
</body>
</html>
