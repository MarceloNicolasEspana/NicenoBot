<script setup>
import { computed } from 'vue';

const props = defineProps({
    // Fuentes tal como las entrega Laravel: { type, reference }. Nunca se
    // construyen ni se infieren en el frontend.
    sources: { type: Array, default: () => [] },
});

const items = computed(() =>
    props.sources
        .filter((s) => s && (s.reference || s.type))
        .map((s) => {
            const ref = (s.reference ?? '').toString().trim();
            const type = (s.type ?? '').toString().trim();
            if (ref && type) return `${type}: ${ref}`;
            return ref || type;
        })
        .filter(Boolean),
);
</script>

<template>
    <p v-if="items.length" class="chat-sources">
        <span class="chat-sources__label">Fuentes:</span>
        <span class="chat-sources__list">{{ items.join(' · ') }}</span>
    </p>
</template>

<style scoped>
.chat-sources {
    margin-top: 0.65rem;
    font-size: 0.78rem;
    line-height: 1.5;
    color: var(--niceno-muted);
}

.chat-sources__label {
    font-weight: 700;
    color: var(--niceno-burgundy);
}

.chat-sources__list {
    margin-left: 0.3rem;
}
</style>
