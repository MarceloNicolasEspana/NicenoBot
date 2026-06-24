<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Nicenito')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-100 text-slate-800">
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.nicenito.dashboard') }}" class="text-lg font-bold text-slate-900">
                        Nicenito · Panel
                    </a>
                    <nav class="hidden gap-4 text-sm font-medium text-slate-500 sm:flex">
                        <a href="{{ route('admin.nicenito.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                        <a href="{{ route('admin.nicenito.contenidos.index') }}" class="hover:text-slate-900">Contenidos</a>
                        <a href="{{ route('admin.nicenito.participantes.index') }}" class="hover:text-slate-900">Participantes</a>
                        <a href="{{ route('admin.nicenito.preguntas.index') }}" class="hover:text-slate-900">Preguntas</a>
                    </nav>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-slate-500">{{ auth()->user()?->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-50">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
