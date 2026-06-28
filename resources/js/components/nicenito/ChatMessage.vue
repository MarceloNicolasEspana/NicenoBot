<script setup>
import { computed } from 'vue';
import NicenitoAvatar from './NicenitoAvatar.vue';
import ChatSources from './ChatSources.vue';
import ReflectionCard from './ReflectionCard.vue';

const props = defineProps({
    role: { type: String, required: true }, // 'user' | 'assistant'
    content: { type: String, default: '' },
    sources: { type: Array, default: () => [] },
    reflection: { type: String, default: null },
    basePath: { type: String, default: '/images/nicenito/clean/' },
    animate: { type: Boolean, default: true },
});

const isUser = computed(() => props.role === 'user');

// Convierte texto plano en bloques seguros (párrafos y listas simples). NUNCA
// se interpreta HTML: Vue escapa el contenido con interpolación de texto.
const blocks = computed(() => {
    const text = (props.content ?? '').replace(/\r\n/g, '\n').trim();
    if (!text) return [];

    const result = [];
    let listBuffer = [];

    const flushList = () => {
        if (listBuffer.length) {
            result.push({ type: 'ul', items: listBuffer });
            listBuffer = [];
        }
    };

    text.split('\n').forEach((rawLine) => {
        const line = rawLine.trim();
        if (!line) {
            flushList();
            return;
        }
        const listMatch = line.match(/^([-•*]|\d+[.)])\s+(.*)$/);
        if (listMatch) {
            listBuffer.push(listMatch[2]);
        } else {
            flushList();
            result.push({ type: 'p', text: line });
        }
    });
    flushList();

    return result;
});
</script>

<template>
    <article
        class="chat-message"
        :class="[isUser ? 'chat-message--user' : 'chat-message--bot', { 'chat-message--animate': animate && !isUser }]"
    >
        <NicenitoAvatar
            v-if="!isUser"
            state="base"
            size="compact"
            :show-status-text="false"
            :base-path="basePath"
            class="chat-message__avatar"
        />
        <div class="chat-message__bubble" :class="isUser ? 'is-user' : 'is-bot'">
            <template v-for="(block, index) in blocks" :key="index">
                <ul v-if="block.type === 'ul'" class="chat-message__list">
                    <li v-for="(item, i) in block.items" :key="i">{{ item }}</li>
                </ul>
                <p v-else class="chat-message__paragraph">{{ block.text }}</p>
            </template>
            <ChatSources v-if="!isUser" :sources="sources" />
            <ReflectionCard v-if="!isUser && reflection" :reflection="reflection" />
        </div>
    </article>
</template>

<style scoped>
.chat-message {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
}

.chat-message--user {
    justify-content: flex-end;
}

.chat-message__avatar {
    flex-shrink: 0;
}

.chat-message__bubble {
    max-width: min(82%, 40rem);
    padding: 0.9rem 1.1rem;
    border-radius: 1.25rem;
    font-size: 0.95rem;
    line-height: 1.65;
    box-shadow: 0 8px 20px rgba(17, 24, 39, 0.07);
}

.chat-message__bubble.is-bot {
    border: 1px solid rgba(217, 203, 182, 0.75);
    border-top-left-radius: 0.4rem;
    background: rgba(255, 247, 232, 0.9);
    color: #233044;
}

.chat-message__bubble.is-user {
    border-bottom-right-radius: 0.4rem;
    background: var(--niceno-burgundy);
    color: #fff;
}

.chat-message__paragraph + .chat-message__paragraph,
.chat-message__paragraph + .chat-message__list,
.chat-message__list + .chat-message__paragraph {
    margin-top: 0.6rem;
}

.chat-message__list {
    margin-top: 0.3rem;
    padding-left: 1.1rem;
    list-style: disc;
}

.chat-message__list li {
    margin-top: 0.15rem;
}

.chat-message--animate {
    animation: chat-message-fade 300ms ease-out;
}

@keyframes chat-message-fade {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .chat-message--animate { animation: none; }
}
</style>
