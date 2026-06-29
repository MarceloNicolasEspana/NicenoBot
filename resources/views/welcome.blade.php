<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>NicenoBot · Apoyo de catequesis</title>
    <meta name="description" content="NicenoBot es un asistente que apoya a jóvenes y laicos en la formación en la fe católica: el Evangelio, la oración y los sacramentos. Conversa con él, gratis y sin registro.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lora:600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: var(--lp-bg); color: var(--lp-text); }</style>
</head>
<body class="min-h-screen">
    {{-- ===== 1. Barra de anuncio ===== --}}
    <div class="w-full text-center text-sm" style="background: var(--lp-text); color: #fdf3e2;">
        <div class="mx-auto max-w-6xl px-4 py-2">
            @if ($weekly)
                <span class="font-semibold">Evangelio de esta semana:</span> {{ $weekly->gospel_reference }} — {{ $weekly->title }}
            @else
                «Vengan a mí todos los que están cansados… y yo los aliviaré.» (Mt 11, 28)
            @endif
        </div>
    </div>

    {{-- ===== 2. Header fijo ===== --}}
    <header class="lp-header">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="#inicio" class="flex items-center gap-2">
                <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="" class="h-9 w-9 object-contain object-top">
                <span class="font-display text-xl font-bold" style="color: var(--lp-text);">NicenoBot</span>
            </a>

            <nav class="hidden items-center gap-7 md:flex" aria-label="Principal">
                <a href="#inicio" class="lp-nav-link">Inicio</a>
                <a href="#como-funciona" class="lp-nav-link">Cómo funciona</a>
                <a href="#catequistas" class="lp-nav-link">Para catequistas</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('participant.access.show') }}" class="btn-primary hidden sm:inline-flex">Conversar con NicenoBot</a>
                <button type="button" data-nav-toggle aria-label="Abrir menú" class="btn-secondary !px-3 md:hidden">≡</button>
            </div>
        </div>

        {{-- Menú móvil --}}
        <div data-nav-menu class="hidden border-t px-4 py-3 md:hidden" style="border-color: var(--lp-card-border);">
            <nav class="flex flex-col gap-1" aria-label="Móvil">
                <a href="#inicio" class="lp-nav-link py-2">Inicio</a>
                <a href="#como-funciona" class="lp-nav-link py-2">Cómo funciona</a>
                <a href="#catequistas" class="lp-nav-link py-2">Para catequistas</a>
                <a href="{{ route('participant.access.show') }}" class="btn-primary mt-2">Conversar con NicenoBot</a>
            </nav>
        </div>
    </header>

    <main id="inicio">
        {{-- ===== 3. Hero ===== --}}
        <section class="mx-auto max-w-6xl px-4 pt-14 pb-10 sm:px-6">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div class="reveal">
                    <p class="lp-eyebrow">Estudio de fe</p>
                    <h1 class="mt-3 font-display text-4xl font-bold leading-tight sm:text-5xl" style="color: var(--lp-text);">
                        Conversa sobre tu fe con NicenoBot
                    </h1>
                    <p class="mt-4 max-w-xl text-lg leading-8" style="color: var(--lp-text-soft);">
                        Un asistente cálido y cercano para entender el <strong>Evangelio</strong>,
                        crecer en la <strong>oración</strong> y descubrir los <strong>sacramentos</strong>.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('participant.access.show') }}" class="btn-primary text-base">Empieza a conversar</a>
                        <a href="#como-funciona" class="btn-secondary text-base">Cómo funciona</a>
                    </div>
                    <p class="mt-4 text-sm" style="color: var(--lp-text-soft);">
                        Gratis · sin registro · tus mensajes se guardan solo en tu navegador
                    </p>
                </div>

                {{-- Preview del chat --}}
                <div class="reveal">
                    <div class="lp-card mx-auto max-w-md p-4 shadow-xl sm:p-5">
                        <div class="flex items-center gap-3 border-b pb-3" style="border-color: var(--lp-card-border);">
                            <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="NicenoBot" class="h-10 w-10 rounded-full object-contain object-top" style="background: var(--lp-surface);">
                            <div>
                                <p class="text-sm font-bold" style="color: var(--lp-text);">NicenoBot</p>
                                <p class="text-xs" style="color: var(--lp-green);">● En línea para ayudarte</p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-4">
                            <div class="chat-row chat-row-bot"><div class="chat-bubble chat-bubble-bot"><p>Hola, soy NicenoBot. ¿Sobre qué te gustaría conversar hoy?</p></div></div>
                            <div class="chat-row chat-row-user"><div class="chat-bubble chat-bubble-user"><p>¿Cómo puedo rezar cuando estoy distraído?</p></div></div>
                            <div class="chat-row chat-row-bot"><div class="chat-bubble chat-bubble-bot"><p>Es muy normal distraerse. Puedes empezar con un minuto de silencio y una frase corta del Evangelio…</p></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== 5. Así te ayuda Nicenito (pestañas) ===== --}}
        <section id="como-funciona" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6">
            <div class="reveal text-center">
                <p class="lp-eyebrow">Capacidades</p>
                <h2 class="mt-2 font-display text-3xl font-bold sm:text-4xl" style="color: var(--lp-text);">Así te ayuda NicenoBot</h2>
            </div>

            @php
                $caps = [
                    ['key' => 'evangelio', 'tab' => 'Explica el Evangelio en simple', 'img' => 'explicando',
                     'q' => '¿Qué quiere decir el Evangelio de hoy?',
                     'a' => 'Jesús nos invita a confiar. En palabras simples: no estás solo, el Padre cuida de ti incluso en lo pequeño.'],
                    ['key' => 'oracion', 'tab' => 'Acompaña tu oración', 'img' => 'escuchando',
                     'q' => 'No sé cómo empezar a rezar.',
                     'a' => 'Puedes hablarle a Dios como a alguien que te ama. Empieza dando gracias por algo de hoy.'],
                    ['key' => 'sacramentos', 'tab' => 'Responde sobre los sacramentos', 'img' => 'respondiendo',
                     'q' => '¿Qué es la confesión?',
                     'a' => 'Es un encuentro con la misericordia de Dios, que sana y da un nuevo comienzo. No es para humillarte.'],
                    ['key' => 'reflexion', 'tab' => 'Propone reflexiones', 'img' => 'pensando',
                     'q' => 'Dame algo para pensar esta semana.',
                     'a' => 'Pregunta para reflexionar: ¿qué miedo te gustaría poner hoy en manos de Dios?'],
                ];
            @endphp

            <div class="reveal mt-10 grid gap-6 lg:grid-cols-[minmax(0,0.4fr)_minmax(0,0.6fr)]">
                {{-- Lista de pestañas --}}
                <div class="flex flex-col gap-2" role="tablist" aria-label="Capacidades de NicenoBot">
                    @foreach ($caps as $i => $cap)
                        <button type="button" class="lp-tab {{ $i === 0 ? 'is-active' : '' }}" role="tab"
                            data-tab="{{ $cap['key'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                            {{ $cap['tab'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Paneles --}}
                <div>
                    @foreach ($caps as $i => $cap)
                        <div class="lp-tab-panel {{ $i === 0 ? 'is-active' : '' }}" data-panel="{{ $cap['key'] }}" role="tabpanel">
                            <div class="lp-card p-5 sm:p-6">
                                <div class="flex items-center gap-3 border-b pb-3" style="border-color: var(--lp-card-border);">
                                    <img src="{{ asset('images/nicenito/clean/'.$cap['img'].'.png') }}" alt="" class="h-12 w-12 rounded-full object-contain object-top" style="background: var(--lp-surface);">
                                    <p class="font-display text-lg font-bold" style="color: var(--lp-text);">{{ $cap['tab'] }}</p>
                                </div>
                                <div class="space-y-3 pt-4">
                                    <div class="chat-row chat-row-user"><div class="chat-bubble chat-bubble-user"><p>{{ $cap['q'] }}</p></div></div>
                                    <div class="chat-row chat-row-bot"><div class="chat-bubble chat-bubble-bot"><p>{{ $cap['a'] }}</p></div></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===== 6. Secciones alternadas imagen/texto ===== --}}
        <section id="catequistas" class="scroll-mt-20" style="background: var(--lp-surface);">
            <div class="mx-auto max-w-6xl space-y-16 px-4 py-16 sm:px-6">
                @php
                    $rows = [
                        ['img' => 'base', 'eyebrow' => 'Fiel y respetuoso',
                         'title' => 'Respuestas fieles a la fe católica',
                         'text' => 'Nicenito responde a partir del contenido preparado por tu equipo de catequesis, con un lenguaje sencillo y reverente. No inventa citas ni doctrina: cuando algo excede su alcance, te invita a conversarlo con un adulto de confianza.',
                         'link' => '#faq', 'linkText' => 'Ver cómo cuida la fidelidad'],
                        ['img' => 'celebrando', 'eyebrow' => 'Para catequistas y grupos',
                         'title' => 'Pensado para catequistas y grupos juveniles',
                         'text' => 'Apoya la preparación de encuentros, ayuda a los jóvenes a formular mejor sus preguntas y refuerza el tema de la semana. El equipo administra el contenido desde un panel propio.',
                         'link' => route('login'), 'linkText' => 'Acceso para catequistas'],
                        ['img' => 'pensando', 'eyebrow' => 'Cercano',
                         'title' => 'NicenoBot escucha, explica y reflexiona contigo',
                         'text' => 'Sus gestos acompañan la conversación —escucha, piensa, explica, celebra— para que sientas cercanía mientras aprendes y rezas.',
                         'link' => route('participant.access.show'), 'linkText' => 'Empieza a conversar'],
                    ];
                @endphp

                @foreach ($rows as $i => $row)
                    <div class="reveal grid items-center gap-8 lg:grid-cols-2">
                        <div class="{{ $i % 2 === 1 ? 'lg:order-2' : '' }}">
                            <div class="lp-card mx-auto flex max-w-sm items-end justify-center p-6" style="background: var(--lp-card);">
                                <img src="{{ asset('images/nicenito/clean/'.$row['img'].'.png') }}" alt="Nicenito" class="h-56 w-auto object-contain">
                            </div>
                        </div>
                        <div class="{{ $i % 2 === 1 ? 'lg:order-1' : '' }}">
                            <p class="lp-eyebrow">{{ $row['eyebrow'] }}</p>
                            <h2 class="mt-2 font-display text-2xl font-bold sm:text-3xl" style="color: var(--lp-text);">{{ $row['title'] }}</h2>
                            <p class="mt-3 text-base leading-7" style="color: var(--lp-text-soft);">{{ $row['text'] }}</p>
                            <a href="{{ $row['link'] }}" class="mt-4 inline-flex items-center gap-1 font-semibold hover:underline" style="color: var(--lp-primary);">
                                {{ $row['linkText'] }} →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== 8. Cómo empezar en 3 pasos ===== --}}
        <section class="scroll-mt-20" style="background: var(--lp-surface);">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="reveal text-center">
                    <p class="lp-eyebrow">En tres pasos</p>
                    <h2 class="mt-2 font-display text-3xl font-bold sm:text-4xl" style="color: var(--lp-text);">Cómo empezar</h2>
                </div>

                @php
                    $pasos = [
                        ['t' => 'Abre NicenoBot', 'd' => 'Entra con el código y PIN que te dio tu catequista.', 's' => 'Sugerencia: si es tu primer ingreso, crearás tu PIN personal.'],
                        ['t' => 'Haz tu pregunta', 'd' => 'Escribe lo que te inquieta sobre la fe, la oración o el Evangelio.', 's' => 'Sugerencia: usa las preguntas sugeridas si no sabes cómo empezar.'],
                        ['t' => 'Reflexiona y reza', 'd' => 'Quédate con una frase y llévala a la oración durante la semana.', 's' => 'Sugerencia: comparte tus dudas grandes con tu catequista o sacerdote.'],
                    ];
                @endphp
                <div class="reveal mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($pasos as $i => $paso)
                        <div class="lp-card flex h-full flex-col p-6">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full font-display text-base font-bold text-white" style="background: var(--lp-primary);">{{ $i + 1 }}</span>
                            <h3 class="mt-4 font-display text-lg font-bold" style="color: var(--lp-text);">{{ $paso['t'] }}</h3>
                            <p class="mt-1 text-sm leading-6" style="color: var(--lp-text-soft);">{{ $paso['d'] }}</p>
                            <p class="mt-3 rounded-lg px-3 py-2 text-xs leading-5" style="background: var(--lp-bg); color: var(--lp-green); border: 1px solid var(--lp-card-border);">{{ $paso['s'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===== 9. Banda de CTA final ===== --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="reveal flex flex-col items-center gap-5 rounded-3xl px-6 py-12 text-center" style="background: var(--lp-text);">
                <img src="{{ asset('images/nicenito/clean/celebrando.png') }}" alt="" class="h-24 w-auto object-contain">
                <h2 class="font-display text-3xl font-bold sm:text-4xl" style="color: #fdf3e2;">Comienza a conversar con NicenoBot</h2>
                <p class="max-w-xl text-base" style="color: #f0dcc0;">Gratis, sin registro y a tu ritmo.</p>
                <a href="{{ route('participant.access.show') }}" class="btn-primary text-base">Conversar con NicenoBot</a>
            </div>
        </section>
    </main>

    {{-- ===== 11. Footer ===== --}}
    <footer style="background: var(--lp-text); color: #f0dcc0;">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="" class="h-9 w-9 object-contain object-top">
                        <span class="font-display text-xl font-bold" style="color: #fdf3e2;">Nicenito</span>
                    </div>
                    <p class="mt-3 text-sm leading-6">Apoyo de catequesis para crecer en la fe.</p>
                </div>
                <div>
                    <p class="text-sm font-bold" style="color: #fdf3e2;">Secciones</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="#inicio" class="hover:underline">Inicio</a></li>
                        <li><a href="#como-funciona" class="hover:underline">Cómo funciona</a></li>
                        <li><a href="#catequistas" class="hover:underline">Para catequistas</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-bold" style="color: #fdf3e2;">Recursos</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('participant.access.show') }}" class="hover:underline">Conversar con Nicenito</a></li>
                        <li><a href="{{ route('login') }}" class="hover:underline">Acceso para catequistas</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t pt-6 text-xs leading-6" style="border-color: rgba(253,243,226,0.18);">
                Este chatbot es una ayuda para aprender y reflexionar. No reemplaza la conversación con tu
                catequista, sacerdote o adulto responsable.
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const root = document.documentElement;
            root.classList.add('js');
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Menú móvil
            const toggle = document.querySelector('[data-nav-toggle]');
            const menu = document.querySelector('[data-nav-menu]');
            if (toggle && menu) {
                toggle.addEventListener('click', () => menu.classList.toggle('hidden'));
                menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => menu.classList.add('hidden')));
            }

            // Pestañas de capacidades
            document.querySelectorAll('[data-tab]').forEach((tab) => {
                tab.addEventListener('click', () => {
                    const key = tab.dataset.tab;
                    document.querySelectorAll('[data-tab]').forEach((t) => {
                        const active = t === tab;
                        t.classList.toggle('is-active', active);
                        t.setAttribute('aria-selected', active ? 'true' : 'false');
                    });
                    document.querySelectorAll('[data-panel]').forEach((p) =>
                        p.classList.toggle('is-active', p.dataset.panel === key));
                });
            });

            // Animación al hacer scroll
            const reveals = document.querySelectorAll('.reveal');
            if (reduce || !('IntersectionObserver' in window)) {
                reveals.forEach((el) => el.classList.add('is-visible'));
            } else {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
                    });
                }, { threshold: 0.12 });
                reveals.forEach((el) => io.observe(el));
            }
        })();
    </script>
</body>
</html>
