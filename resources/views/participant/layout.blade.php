<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso · NicenoBot')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lora:600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: var(--lp-bg); color: var(--lp-text); }</style>
</head>
<body class="min-h-screen">
    <main class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="lp-card w-full max-w-md p-7 shadow-xl sm:p-9">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full" style="background: var(--lp-surface); border: 1px solid var(--lp-border);">
                    <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="NicenoBot" class="h-12 w-12 object-contain object-top">
                </div>
                <div>
                    <p class="lp-eyebrow">NicenoBot</p>
                    <h1 class="font-display text-xl font-bold" style="color: var(--lp-text);">@yield('heading')</h1>
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
    </main>
</body>
</html>
