<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>NicenoBot · Pregúntale a Nicenito</title>
    <meta name="description" content="Nicenito es un asistente de catequesis católica que acompaña a los jóvenes a entender el Evangelio, la oración, los sacramentos y la fe.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lora:600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[var(--niceno-bg-2)] text-[var(--niceno-ink)]">
    <main class="relative min-h-screen overflow-hidden">
        <div class="page-background-image" aria-hidden="true"></div>
        <div class="page-background-veil" aria-hidden="true"></div>

        <div class="relative z-10 mx-auto flex min-h-screen max-w-[1200px] flex-col px-4 py-6 sm:px-6 lg:px-8">
            {{-- Barra superior --}}
            <header class="flex items-center justify-between gap-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-[color:var(--niceno-gold-soft)] bg-[color:var(--niceno-cream)]/86 px-4 py-2 text-sm font-semibold text-[var(--niceno-burgundy)]">
                    <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="" class="h-6 w-6 object-contain object-top">
                    NicenoBot
                </span>
                <a
                    href="{{ route('login') }}"
                    class="rounded-full border border-[color:var(--niceno-border)] bg-white/70 px-4 py-2 text-sm font-semibold text-[var(--niceno-burgundy)] backdrop-blur transition hover:bg-white"
                >
                    Acceso catequistas
                </a>
            </header>

            {{-- Héroe --}}
            <section class="grid flex-1 items-center gap-8 py-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.85fr)] lg:gap-10">
                <div class="order-2 lg:order-1">
                    <div class="niceno-shell !h-auto !min-h-0 p-7 sm:p-9">
                        <p class="text-sm font-semibold uppercase tracking-wide text-[var(--niceno-burgundy)]">
                            Catequesis acompañada
                        </p>
                        <h1 class="mt-3 font-display text-4xl font-bold leading-tight text-[var(--niceno-ink)] sm:text-5xl">
                            Pregúntale a Nicenito
                        </h1>
                        <p class="mt-4 max-w-xl text-base leading-7 text-[var(--niceno-muted)]">
                            Un asistente para tu camino de fe. Conversa sobre el Evangelio del domingo,
                            la oración, los sacramentos y tus dudas, con respuestas sencillas y cercanas
                            preparadas desde el contenido de tu catequesis.
                        </p>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('participant.access.show') }}"
                                class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-[var(--niceno-burgundy)] px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(91,26,31,0.24)] transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]"
                            >
                                Ingresar para conversar
                            </a>
                            <a
                                href="#como-funciona"
                                class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-[color:var(--niceno-border)] bg-white/70 px-6 py-3 text-sm font-bold text-[var(--niceno-ink)] transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]"
                            >
                                Cómo funciona
                            </a>
                        </div>

                        <p class="mt-5 text-xs leading-5 text-[var(--niceno-muted)]">
                            ¿Eres joven de catequesis? Pide tu código y PIN a tu catequista para entrar.
                        </p>
                    </div>
                </div>

                {{-- Presencia de Nicenito --}}
                <div class="order-1 flex justify-center lg:order-2">
                    <div class="nicenito-presence nicenito--explicando">
                        <div class="nicenito-arch mx-auto">
                            <div class="nicenito-halo" aria-hidden="true"></div>
                            <img
                                src="{{ asset('images/nicenito/clean/explicando.png') }}"
                                alt="Nicenito te acompaña"
                                class="nicenito-stage-image"
                            >
                        </div>
                    </div>
                </div>
            </section>

            {{-- Cómo funciona: instructivo paso a paso --}}
            <section id="como-funciona" class="scroll-mt-6 pb-10">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-[var(--niceno-ink-warm)] sm:text-4xl">Cómo funciona</h2>
                    <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[var(--niceno-muted-warm)]">
                        Así acompaña Nicenito hoy a los jóvenes de catequesis, paso a paso.
                    </p>
                </div>

                <ol class="mt-7 grid gap-4 md:grid-cols-2">
                    @php
                        $pasos = [
                            [
                                't' => 'Ingresa con tu código y PIN',
                                'd' => 'Tu catequista te entrega un código personal (por ejemplo NCE-7F4K) y un PIN. En tu primer acceso creas tu PIN propio y aceptas un breve aviso de privacidad. No se piden correos ni datos personales.',
                            ],
                            [
                                't' => 'Escribe tu pregunta',
                                'd' => 'Puedes preguntar sobre el Evangelio del domingo, la oración, los sacramentos o cualquier duda de fe. Hay sugerencias rápidas para partir, y cada mensaje admite hasta 500 caracteres.',
                            ],
                            [
                                't' => 'Nicenito responde desde tu catequesis',
                                'd' => 'Busca primero en el contenido preparado por tu equipo (el tema semanal y temas fijos de doctrina) y, con ese contexto, redacta una respuesta sencilla y cercana. Si solo saludas, responde breve sin más.',
                            ],
                            [
                                't' => 'Verás las fuentes y, si hace falta, una guía',
                                'd' => 'Cada respuesta puede mostrar sus fuentes (Evangelio, Biblia o Catecismo). Si una pregunta excede lo disponible, Nicenito te recomienda conversarla con tu catequista, sacerdote o un adulto de confianza.',
                            ],
                        ];
                    @endphp
                    @foreach ($pasos as $i => $paso)
                        <li class="flex gap-4 rounded-2xl border border-[color:var(--niceno-gold-soft)] bg-[color:var(--niceno-cream)]/92 p-5 backdrop-blur">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--niceno-burgundy)] font-display text-base font-bold text-white">
                                {{ $i + 1 }}
                            </span>
                            <div>
                                <h3 class="font-display text-lg font-bold text-[var(--niceno-burgundy)]">{{ $paso['t'] }}</h3>
                                <p class="mt-1 text-sm leading-6 text-[var(--niceno-muted)]">{{ $paso['d'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{-- Sobre qué puedes preguntar --}}
                <h3 class="mt-10 text-center font-display text-2xl font-bold text-[var(--niceno-ink-warm)]">Sobre qué puedes preguntar</h3>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $temas = [
                            ['t' => 'El Evangelio', 'd' => 'Entiende la lectura del domingo y qué te dice Jesús hoy.'],
                            ['t' => 'La oración', 'd' => 'Aprende a rezar con tus palabras y a hacerle un espacio a Dios.'],
                            ['t' => 'Los sacramentos', 'd' => 'Confesión, Eucaristía, Confirmación y el sentido de cada uno.'],
                            ['t' => 'Tus dudas de fe', 'd' => 'Pregunta con confianza; Nicenito te orienta con respeto.'],
                        ];
                    @endphp
                    @foreach ($temas as $tema)
                        <div class="rounded-2xl border border-[color:var(--niceno-gold-soft)] bg-[color:var(--niceno-cream)]/90 p-5 backdrop-blur">
                            <h4 class="font-display text-lg font-bold text-[var(--niceno-burgundy)]">{{ $tema['t'] }}</h4>
                            <p class="mt-2 text-sm leading-6 text-[var(--niceno-muted)]">{{ $tema['d'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-col items-center gap-4 rounded-2xl bg-[var(--niceno-ink)]/92 px-6 py-6 text-center backdrop-blur">
                    <p class="text-sm leading-6 text-white/90">
                        Nicenito es una ayuda para aprender y reflexionar. No reemplaza la conversación con tu
                        catequista, sacerdote o adulto responsable. El nombre recuerda el Concilio de Nicea:
                        <span class="text-white">Nicea → Niceno → Nicenito</span>.
                    </p>
                    <a
                        href="{{ route('participant.access.show') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-[var(--niceno-burgundy)] px-6 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(91,26,31,0.24)] transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]"
                    >
                        Ingresar para conversar
                    </a>
                </div>
            </section>

            <footer class="mt-auto py-4 text-center text-xs text-[var(--niceno-muted-warm)]">
                NicenoBot · Acompañamiento de catequesis
            </footer>
        </div>
    </main>
</body>
</html>
