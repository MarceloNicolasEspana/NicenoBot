<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso · Nicenito')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lora:600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-950 text-[var(--niceno-ink)]" style="font-family: 'instrument-sans', ui-sans-serif, system-ui, sans-serif;">
    <main class="relative min-h-screen overflow-hidden">
        <div class="page-background-image"
            style="background-image: url('{{ asset('images/nicenito/basilica_san_pedro.png') }}')" aria-hidden="true"></div>
        <div class="page-background-veil" aria-hidden="true"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8">
            <div class="w-full max-w-md rounded-3xl border border-[color:var(--niceno-gold-soft)] bg-white/92 p-7 shadow-2xl sm:p-9">
                <div class="flex items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[color:var(--niceno-cream)] ring-1 ring-[color:var(--niceno-gold-soft)]">
                        <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="Nicenito" class="h-12 w-12 object-contain object-top">
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--niceno-burgundy)]">NicenoBot</p>
                        <h1 class="text-xl font-bold text-[var(--niceno-ink)]">@yield('heading')</h1>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mt-6">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>
</body>
</html>
