<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de NicenoBot')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>body { font-family: ui-sans-serif, system-ui, sans-serif; }</style>
</head>
@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user?->name ?? $user?->email ?? '?')))
        ->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    $icons = [
        'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25Zm9.75-9.75A2.25 2.25 0 0 1 15.75 3.75H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
        'contenidos' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>',
        'participantes' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
        'preguntas' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>',
    ];
    $nav = [
        ['route' => 'admin.nicenito.dashboard', 'pattern' => 'admin.nicenito.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['route' => 'admin.nicenito.contenidos.index', 'pattern' => 'admin.nicenito.contenidos.*', 'label' => 'Contenidos', 'icon' => 'contenidos'],
        ['route' => 'admin.nicenito.participantes.index', 'pattern' => 'admin.nicenito.participantes.*', 'label' => 'Participantes', 'icon' => 'participantes'],
        ['route' => 'admin.nicenito.preguntas.index', 'pattern' => 'admin.nicenito.preguntas.*', 'label' => 'Preguntas', 'icon' => 'preguntas'],
    ];
@endphp
<body class="admin-shell h-full">
    {{-- Estado inicial del sidebar colapsado (antes de pintar, evita parpadeo) --}}
    <script>
        try { if (localStorage.getItem('adminSidebarCollapsed') === '1') document.body.classList.add('sidebar-collapsed'); } catch (e) {}
    </script>

    {{-- Overlay del drawer (solo móvil) --}}
    <div data-sidebar-close class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden" id="admin-sidebar-overlay"></div>

    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="admin-sidebar">
        <header class="admin-brand">
            <span class="admin-brand-avatar">
                <img src="{{ asset('images/nicenito/clean/base.png') }}" alt="NicenoBot" class="admin-brand-img">
            </span>
            <span>NicenoBot · Panel</span>
        </header>

        <nav class="admin-nav" aria-label="Navegación del panel">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                    class="admin-nav-link {{ request()->routeIs($item['pattern']) ? 'is-active' : '' }}"
                    @if (request()->routeIs($item['pattern'])) aria-current="page" @endif>
                    <span class="admin-nav-icon" aria-hidden="true">{!! $icons[$item['icon']] !!}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Bloque de usuario, al pie --}}
        <footer class="admin-user">
            <div class="flex items-center gap-3">
                <span class="admin-avatar" aria-hidden="true">{{ $initials }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold" style="color: #fff7e9;">{{ $user?->name }}</p>
                    <p class="truncate text-xs" style="color: rgba(255, 247, 233, 0.72);">{{ $user?->email }}</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <a href="{{ route('admin.nicenito.perfil.edit') }}" class="btn-secondary flex-1 !px-3 !py-1.5 text-center text-xs">Mi perfil</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-secondary w-full !px-3 !py-1.5 text-xs">Salir</button>
                </form>
            </div>
        </footer>
    </aside>

    {{-- Botón de colapsar/mostrar el sidebar (escritorio), con efecto slide --}}
    <button type="button" data-sidebar-collapse class="admin-sidebar-toggle"
        aria-label="Ocultar o mostrar el panel" title="Ocultar o mostrar el panel">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
    </button>

    {{-- Contenido principal --}}
    <div class="admin-main">
        {{-- Barra superior móvil con hamburguesa --}}
        <div class="flex items-center gap-3 border-b px-4 py-3 lg:hidden" style="border-color: var(--admin-border); background: var(--admin-surface);">
            <button type="button" data-sidebar-toggle aria-label="Abrir menú"
                class="rounded-lg border px-3 py-1.5 text-sm font-semibold focus:outline-none"
                style="border-color: var(--admin-border); color: var(--admin-text);">≡ Menú</button>
            <span class="font-bold" style="color: var(--admin-text);">NicenoBot · Panel</span>
        </div>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Modal global (se rellena por AJAX) --}}
    <div class="admin-modal" id="admin-modal" role="dialog" aria-modal="true">
        <div class="admin-modal-panel" id="admin-modal-body"></div>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            const modal = document.getElementById('admin-modal');
            const modalBody = document.getElementById('admin-modal-body');

            // ---- Sidebar (drawer móvil) ----
            const openSidebar = () => { sidebar.classList.add('is-open'); overlay.classList.remove('hidden'); };
            const closeSidebar = () => { sidebar.classList.remove('is-open'); overlay.classList.add('hidden'); };

            // ---- Modal ----
            const closeModal = () => { modal.classList.remove('is-open'); modalBody.innerHTML = ''; };

            const renderErrors = (box, errors) => {
                if (!box) return;
                const msgs = [];
                Object.values(errors || {}).forEach((arr) => Array.isArray(arr) ? msgs.push(...arr) : msgs.push(arr));
                box.innerHTML = '<ul class="list-disc pl-5">' + msgs.map((m) =>
                    '<li>' + String(m).replace(/[<>&]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;' }[c])) + '</li>').join('') + '</ul>';
                box.classList.remove('hidden');
                box.scrollIntoView({ block: 'nearest' });
            };

            const wireForm = (scope) => {
                // Conmutador semanal/fijo del formulario de contenidos
                const select = scope.querySelector('#type-select');
                if (select) {
                    const toggle = () => scope.querySelectorAll('[data-type-block]').forEach((el) => {
                        el.style.display = el.dataset.typeBlock === select.value ? '' : 'none';
                    });
                    select.addEventListener('change', toggle);
                    toggle();
                }

                const form = scope.querySelector('[data-ajax-form]');
                if (!form) return;
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const fd = new FormData(form);
                    if (e.submitter && e.submitter.name) fd.append(e.submitter.name, e.submitter.value);
                    const errBox = form.querySelector('[data-form-errors]');
                    if (errBox) { errBox.classList.add('hidden'); errBox.innerHTML = ''; }
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        });
                        if (res.ok) {
                            const data = await res.json().catch(() => ({}));
                            window.location = data.redirect || window.location.href;
                            return;
                        }
                        if (res.status === 422) { renderErrors(errBox, (await res.json()).errors); return; }
                        renderErrors(errBox, { e: ['Ocurrió un error al guardar. Intenta de nuevo.'] });
                    } catch (_) {
                        renderErrors(errBox, { e: ['No se pudo conectar. Revisa tu conexión.'] });
                    }
                });
            };

            const openModal = async (url) => {
                modalBody.innerHTML = '<div class="p-8 text-center text-sm" style="color: var(--admin-text-soft);">Cargando…</div>';
                modal.classList.add('is-open');
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    modalBody.innerHTML = await res.text();
                    wireForm(modalBody);
                } catch (_) {
                    modalBody.innerHTML = '<div class="p-8 text-center text-sm text-rose-700">No se pudo cargar el formulario.</div>';
                }
            };

            // ---- Delegación de eventos ----
            document.addEventListener('click', (e) => {
                const collapse = e.target.closest('[data-sidebar-collapse]');
                if (collapse) {
                    const collapsed = document.body.classList.toggle('sidebar-collapsed');
                    try { localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0'); } catch (_) {}
                    return;
                }

                const toggle = e.target.closest('[data-sidebar-toggle]');
                if (toggle) { openSidebar(); return; }
                if (e.target.closest('[data-sidebar-close]')) { closeSidebar(); return; }

                const trigger = e.target.closest('[data-modal-url]');
                if (trigger) { e.preventDefault(); openModal(trigger.getAttribute('data-modal-url')); return; }
                if (e.target === modal || e.target.closest('[data-modal-close]')) { closeModal(); }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') { closeModal(); closeSidebar(); }
            });
        })();
    </script>
</body>
</html>
