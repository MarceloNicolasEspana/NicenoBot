<script setup>
import { nextTick, ref, watch } from 'vue';
import ChatMessage from './ChatMessage.vue';
import ChatEmptyState from './ChatEmptyState.vue';

const props = defineProps({
    messages: { type: Array, default: () => [] },
    isSending: { type: Boolean, default: false },
    brandName: { type: String, default: 'NicenoBot' },
    displayName: { type: String, default: '' },
    basePath: { type: String, default: '/images/nicenito/clean/' },
    reducedMotion: { type: Boolean, default: false },
});

const scroller = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        const el = scroller.value;
        if (el) el.scrollTop = el.scrollHeight;
    });
};

watch(
    () => [props.messages.length, props.isSending],
    scrollToBottom,
);
</script>

<template>
    <div ref="scroller" class="chat-list" role="log" aria-live="polite">
        <ChatEmptyState
            v-if="messages.length === 0"
            :brand-name="brandName"
            :display-name="displayName"
            :base-path="basePath"
        />

        <ChatMessage
            v-for="message in messages"
            :key="message.id"
            :role="message.role"
            :content="message.content"
            :sources="message.sources"
            :reflection="message.reflection"
            :base-path="basePath"
            :animate="!reducedMotion"
        />

        <div v-if="isSending" class="chat-list__loading">
            <span class="chat-list__dot" aria-hidden="true"></span>
            {{ brandName }} está preparando una respuesta…
        </div>
    </div>
</template>

<style scoped>
.chat-list {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.25rem;
}

.chat-list__loading {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    align-self: flex-start;
    padding: 0.5rem 0.95rem;
    border-radius: 9999px;
    background: var(--niceno-cream);
    box-shadow: inset 0 0 0 1px var(--niceno-gold-soft);
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--niceno-burgundy);
}

.chat-list__dot {
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 9999px;
    background: var(--niceno-gold);
    animation: chat-list-pulse 1s ease-in-out infinite;
}

@keyframes chat-list-pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .chat-list__dot { animation: none; }
}
</style>
