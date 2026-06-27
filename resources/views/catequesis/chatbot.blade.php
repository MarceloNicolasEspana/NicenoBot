<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Preg&uacute;ntale a NicenoBot</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lora:600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[var(--lp-bg)] text-[var(--niceno-ink)]">
        <main class="relative min-h-screen overflow-hidden">
            <div class="relative z-10 mx-auto flex min-h-screen max-w-[1320px] items-center px-4 py-5 sm:px-6 lg:px-8">
                <section
                    id="catequesis-chat"
                    data-endpoint="{{ route('chatbot.chat') }}"
                    data-access-url="{{ route('participant.access.show') }}"
                    class="niceno-shell grid w-full overflow-hidden lg:grid-cols-[minmax(0,0.38fr)_minmax(0,0.62fr)]"
                >
                    <aside class="niceno-stage flex min-h-[34rem] flex-col justify-between p-5 sm:p-7 lg:min-h-[44rem] lg:p-8">
                        <div class="space-y-5">
                            <h1 class="text-3xl font-bold text-[var(--niceno-ink)] sm:text-4xl">
                                Preg&uacute;ntale a NicenoBot
                            </h1>
                            <div class="space-y-3">
                                <p class="max-w-md text-base leading-7 text-[var(--niceno-muted)]">
                                    Haz una pregunta sobre el Evangelio, la fe, la oraci&oacute;n o los sacramentos.
                                </p>
                            </div>
                        </div>

                        <div
                            id="nicenito-avatar"
                            data-state="base"
                            class="nicenito-presence nicenito--base mt-6"
                        >
                            <div class="nicenito-arch mx-auto">
                                <div class="nicenito-halo" aria-hidden="true"></div>
                                <img
                                    id="nicenito-avatar-image"
                                    src="{{ asset('images/nicenito/clean/base.png') }}"
                                    alt="NicenoBot est&aacute; en reposo"
                                    class="nicenito-avatar-image nicenito-stage-image"
                                >
                            </div>

                            <div class="nicenito-status" aria-live="polite">
                                <p id="nicenito-avatar-label">NicenoBot est&aacute; listo para acompa&ntilde;arte.</p>
                                <span class="nicenito-status-dots" aria-hidden="true">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </div>
                        </div>
                    </aside>

                    <section class="niceno-chat-panel flex min-h-0 flex-col">
                        <header class="border-b border-[color:var(--niceno-border)]/80 px-5 py-4 sm:px-7">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[color:var(--niceno-cream)] ring-1 ring-[color:var(--niceno-gold-soft)]">
                                    <img
                                        src="{{ asset('images/nicenito/clean/base.png') }}"
                                        alt="NicenoBot"
                                        class="h-10 w-10 object-contain object-top"
                                    >
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h2 class="text-lg font-bold text-[var(--niceno-ink)]">Conversa con NicenoBot</h2>
                                    <p class="mt-1 text-sm text-[var(--niceno-muted)]">Tus mensajes se guardan solo en este navegador.</p>
                                </div>
                                <form method="POST" action="{{ route('participant.logout') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="rounded-full border border-[color:var(--niceno-border)] px-3 py-1.5 text-xs font-semibold text-[var(--niceno-muted)] transition hover:bg-[color:var(--niceno-cream)]">
                                        Salir
                                    </button>
                                </form>
                            </div>
                        </header>

                        <div class="chat-suggestions border-b border-[color:var(--niceno-border)]/70 px-5 py-4 sm:px-7">
                            <div class="flex flex-wrap gap-2.5">
                                <button type="button" class="suggested-question niceno-chip" data-question="Explicame el Evangelio del domingo">
                                    Expl&iacute;came el Evangelio del domingo
                                </button>
                                <button type="button" class="suggested-question niceno-chip" data-question="Como puedo rezar mejor?">
                                    &iquest;C&oacute;mo puedo rezar mejor?
                                </button>
                                <button type="button" class="suggested-question niceno-chip" data-question="Que es la confesion?">
                                    &iquest;Qu&eacute; es la confesi&oacute;n?
                                </button>
                                <button type="button" class="suggested-question niceno-chip" data-question="Que significa tener fe?">
                                    &iquest;Qu&eacute; significa tener fe?
                                </button>
                                <button type="button" class="suggested-question niceno-chip" data-question="Dame una pregunta para reflexionar">
                                    Dame una pregunta para reflexionar
                                </button>
                            </div>
                        </div>

                        <div id="chat-messages" class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7">
                            <article class="chat-row chat-row-bot">
                                <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="" class="chat-mini-avatar">
                                <div class="chat-bubble chat-bubble-bot">
                                    <p>
                                        Hola, soy NicenoBot. Puedes preguntarme sobre la fe, la oraci&oacute;n, Jes&uacute;s, el Evangelio o los sacramentos.
                                    </p>
                                </div>
                            </article>
                        </div>

                        <div id="chat-loading" class="hidden px-5 pb-3 sm:px-7">
                            <div class="inline-flex items-center gap-3 rounded-full bg-[color:var(--niceno-cream)] px-4 py-2 text-sm font-medium text-[var(--niceno-burgundy)] ring-1 ring-[color:var(--niceno-gold-soft)]">
                                <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-[var(--niceno-gold)]"></span>
                                NicenoBot est&aacute; preparando una respuesta...
                            </div>
                        </div>

                        <div id="chat-error" class="hidden px-5 pb-3 text-sm font-semibold text-rose-700 sm:px-7"></div>

                        <form id="chat-form" class="border-t border-[color:var(--niceno-border)]/80 bg-white/72 p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                <label for="chat-message" class="sr-only">Escribe tu pregunta</label>
                                <textarea
                                    id="chat-message"
                                    name="message"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Escribe tu pregunta aqu&iacute;..."
                                    class="min-h-24 flex-1 rounded-2xl border border-[color:var(--niceno-border)] bg-white/84 px-4 py-3 text-sm leading-6 text-[var(--niceno-ink)] outline-none transition placeholder:text-slate-400 focus:border-[color:var(--niceno-gold)] focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)]"
                                ></textarea>
                                <div class="flex items-end gap-3 sm:w-auto sm:flex-col sm:gap-2">
                                    <button
                                        type="submit"
                                        class="chat-send inline-flex min-h-12 min-w-32 items-center justify-center rounded-2xl bg-[var(--niceno-burgundy)] px-5 py-3 text-sm font-bold text-white shadow-[0_12px_24px_rgba(91,26,31,0.24)] transition hover:bg-[var(--niceno-burgundy-dark)] focus:outline-none focus:ring-4 focus:ring-[color:var(--niceno-gold-soft)] active:translate-y-px disabled:cursor-not-allowed disabled:bg-slate-400"
                                        aria-label="Enviar pregunta"
                                    >
                                        <span class="chat-send-text">Enviar</span>
                                        <svg class="chat-send-icon hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 2 11 13"/>
                                            <path d="M22 2 15 22l-4-9-9-4 20-7Z"/>
                                        </svg>
                                    </button>
                                    <p class="text-right text-xs text-[var(--niceno-muted)]">
                                        <span id="chat-counter">0</span>/500
                                    </p>
                                </div>
                            </div>
                        </form>

                        <div class="chat-disclaimer bg-[var(--niceno-ink)] px-5 py-4 text-sm leading-6 text-white/92 sm:px-7">
                            Este chatbot es una ayuda para aprender y reflexionar. No reemplaza la conversaci&oacute;n con tu catequista, sacerdote o adulto responsable.
                        </div>
                    </section>
                </section>
            </div>
        </main>
    </body>
</html>
