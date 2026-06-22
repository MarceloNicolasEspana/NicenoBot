<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Preg&uacute;ntale a Nicenito</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[linear-gradient(180deg,#f6efe3_0%,#fffaf2_40%,#eef6f8_100%)] text-slate-900">
        <main class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(circle_at_top,#f6c76f_0%,rgba(246,199,111,0.24)_35%,transparent_70%)]"></div>
            <div class="absolute right-0 top-24 -z-10 h-64 w-64 rounded-full bg-cyan-200/30 blur-3xl"></div>
            <div class="absolute left-0 top-64 -z-10 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>

            <div class="mx-auto flex min-h-screen max-w-[1280px] items-center px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <section class="flex w-full flex-col gap-5 xl:gap-6">
                    <section class="relative overflow-hidden rounded-[2rem] border border-white/75 bg-white/72 p-6 shadow-[0_18px_48px_rgba(120,89,33,0.12)] backdrop-blur sm:p-8 lg:p-9">
                        <div class="absolute inset-x-0 bottom-0 h-56 bg-[radial-gradient(circle_at_bottom,rgba(245,158,11,0.16),transparent_70%)]"></div>
                        <div class="relative z-10 flex flex-col gap-6">
                            <span class="inline-flex w-fit items-center rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold tracking-wide text-amber-900">
                                NicenoBot
                            </span>

                            <div class="max-w-3xl space-y-4">
                                <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-[3.4rem]">
                                    Preg&uacute;ntale a Nicenito
                                </h1>
                                <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                                    Haz una pregunta sobre el Evangelio, la fe, la oraci&oacute;n o los sacramentos.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="suggested-question rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-950 transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white" data-question="Explicame el Evangelio del domingo">
                                    Expl&iacute;came el Evangelio del domingo
                                </button>
                                <button type="button" class="suggested-question rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-950 transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white" data-question="Como puedo rezar mejor?">
                                    &iquest;C&oacute;mo puedo rezar mejor?
                                </button>
                                <button type="button" class="suggested-question rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-950 transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white" data-question="Que es la confesion?">
                                    &iquest;Qu&eacute; es la confesi&oacute;n?
                                </button>
                                <button type="button" class="suggested-question rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-950 transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white" data-question="Que significa tener fe?">
                                    &iquest;Qu&eacute; significa tener fe?
                                </button>
                                <button type="button" class="suggested-question rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-medium text-amber-950 transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white" data-question="Dame una pregunta para reflexionar">
                                    Dame una pregunta para reflexionar
                                </button>
                            </div>
                        </div>
                    </section>

                    <section
                        id="catequesis-chat"
                        data-endpoint="{{ url('/api/catequesis/chat') }}"
                        class="relative rounded-[2rem] border border-slate-200/80 bg-white/92 p-3 shadow-[0_18px_54px_rgba(15,23,42,0.12)] backdrop-blur sm:p-4"
                    >
                        <div class="relative flex min-h-[36rem] flex-1 flex-col overflow-hidden rounded-[1.6rem] bg-slate-50 ring-1 ring-slate-200 lg:min-h-[42rem]">
                            <div class="border-b border-slate-200 bg-white/95 px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(180deg,#fff7e6,#f5efe0)] ring-1 ring-amber-100">
                                        <img
                                            src="{{ asset('images/nicenito/clean/base.png') }}"
                                            alt="Nicenito"
                                            class="h-10 w-10 object-contain object-top"
                                        >
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-lg font-semibold text-slate-900">Conversa con Nicenito</h2>
                                        <p class="mt-1 text-sm text-slate-500">Tus mensajes se guardan solo en este navegador.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col lg:flex-row">
                                <aside class="relative z-10 border-l-4 border-amber-200/85 px-1 pt-2 lg:flex lg:w-[19rem] lg:shrink-0 lg:items-end lg:justify-start lg:border-l-0 lg:pl-2 lg:pt-6">
                                    <div class="nicenito-chat-figure relative mx-auto w-full max-w-[16rem] sm:max-w-[18rem] lg:mx-0 lg:max-w-none">
                                        <div class="absolute inset-x-4 bottom-4 h-24 rounded-full bg-[radial-gradient(circle,rgba(217,119,6,0.22),transparent_72%)] blur-2xl"></div>
                                        <div
                                            id="nicenito-avatar"
                                            data-state="base"
                                            class="nicenito-chat-stage nicenito-mobile-overlap relative mx-auto flex h-[220px] items-end justify-center sm:h-[250px] lg:h-[360px]"
                                        >
                                            <img
                                                id="nicenito-avatar-image"
                                                src="{{ asset('images/nicenito/clean/base.png') }}"
                                                alt="Nicenito est&aacute; en reposo"
                                                class="nicenito-avatar-image nicenito-chat-image relative z-10 h-full w-full object-contain object-bottom"
                                            >
                                        </div>
                                    </div>
                                </aside>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6 lg:min-h-[25rem]">
                                        <article class="max-w-[82%] rounded-3xl rounded-bl-md bg-white px-5 py-4 text-sm leading-7 text-slate-700 shadow-sm ring-1 ring-slate-200 sm:max-w-[78%]">
                                            <p>
                                                Hola, soy Nicenito. Puedes preguntarme sobre la fe, la oraci&oacute;n, Jes&uacute;s, el Evangelio o los sacramentos.
                                            </p>
                                        </article>
                                    </div>

                                    <div class="rounded-[1.35rem] bg-white/74 px-5 py-4 ring-1 ring-white/75 mx-4 mb-4 sm:mx-6 lg:mx-6">
                                        <p id="nicenito-avatar-label" class="text-sm font-medium leading-6 text-slate-700">
                                            Nicenito est&aacute; listo para acompa&ntilde;arte.
                                        </p>
                                    </div>

                                    <div id="chat-loading" class="hidden px-5 pb-2 sm:px-6">
                                        <div class="inline-flex items-center gap-3 rounded-full bg-cyan-50 px-4 py-2 text-sm text-cyan-900 ring-1 ring-cyan-100">
                                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-cyan-500"></span>
                                            Nicenito est&aacute; preparando una respuesta...
                                        </div>
                                    </div>

                                    <div id="chat-error" class="hidden px-5 pb-2 text-sm font-medium text-rose-700 sm:px-6"></div>

                                    <form id="chat-form" class="border-t border-slate-200 bg-white p-4 sm:p-5">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                            <label for="chat-message" class="sr-only">Escribe tu pregunta</label>
                                            <textarea
                                                id="chat-message"
                                                name="message"
                                                rows="2"
                                                maxlength="500"
                                                placeholder="Escribe tu pregunta aqu&iacute;..."
                                                class="min-h-24 flex-1 rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                                            ></textarea>
                                            <div class="flex items-end gap-3 sm:w-auto sm:flex-col sm:gap-2">
                                                <button
                                                    type="submit"
                                                    class="inline-flex min-h-12 min-w-32 items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
                                                >
                                                    Enviar
                                                </button>
                                                <p class="text-right text-xs text-slate-400">
                                                    <span id="chat-counter">0</span>/500
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-[1.5rem] border border-slate-200/80 bg-slate-950 px-5 py-4 text-sm leading-6 text-slate-100">
                            Este chatbot es una ayuda para aprender y reflexionar. No reemplaza la conversaci&oacute;n con tu catequista, sacerdote o adulto responsable.
                        </div>
                    </section>
                </section>
            </div>
        </main>
    </body>
</html>
