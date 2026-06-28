<script setup>
import NicenitoAvatar from './NicenitoAvatar.vue';

defineProps({
    brandName: { type: String, default: 'NicenoBot' },
    subtitle: { type: String, default: 'Tus mensajes se guardan solo en este navegador.' },
    isSending: { type: Boolean, default: false },
    basePath: { type: String, default: '/images/nicenito/clean/' },
    logoutUrl: { type: String, default: '' },
    csrfToken: { type: String, default: '' },
});
</script>

<template>
    <header class="chat-header">
        <NicenitoAvatar
            state="base"
            size="compact"
            :show-status-text="false"
            :base-path="basePath"
            class="chat-header__avatar"
        />
        <div class="chat-header__text">
            <h2 class="chat-header__title">{{ brandName }}</h2>
            <p class="chat-header__subtitle">
                <span
                    v-if="isSending"
                    class="chat-header__pulse"
                    aria-hidden="true"
                ></span>
                {{ isSending ? 'Preparando una respuesta…' : subtitle }}
            </p>
        </div>
        <form
            v-if="logoutUrl"
            class="chat-header__logout"
            method="POST"
            :action="logoutUrl"
        >
            <input type="hidden" name="_token" :value="csrfToken" />
            <button type="submit" class="chat-header__logout-btn">Salir</button>
        </form>
    </header>
</template>

<style scoped>
.chat-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(217, 203, 182, 0.8);
}

.chat-header__text {
    min-width: 0;
    flex: 1;
}

.chat-header__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--niceno-ink);
}

.chat-header__subtitle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.15rem;
    font-size: 0.85rem;
    color: var(--niceno-muted);
}

.chat-header__pulse {
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 9999px;
    background: var(--niceno-gold);
    animation: chat-header-pulse 1s ease-in-out infinite;
}

.chat-header__logout-btn {
    flex-shrink: 0;
    border: 1px solid var(--niceno-border);
    border-radius: 9999px;
    padding: 0.35rem 0.85rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--niceno-muted);
    transition: background-color 150ms ease;
}

.chat-header__logout-btn:hover {
    background: var(--niceno-cream);
}

.chat-header__logout-btn:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

@keyframes chat-header-pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .chat-header__pulse { animation: none; }
}

/* En celular el hero de NicenoBot va justo debajo: evitamos repetir el avatar
   en el header. */
@media (max-width: 560px) {
    .chat-header__avatar { display: none; }
}
</style>
