<script setup>
defineProps({
    questions: { type: Array, default: () => [] },
});

// Al elegir una sugerencia, la página decide si la copia al input o la envía.
// Las sugerencias NO llaman a Gemini por sí mismas.
const emit = defineEmits(['select']);
</script>

<template>
    <div v-if="questions.length" class="suggested">
        <button
            v-for="(question, index) in questions"
            :key="index"
            type="button"
            class="suggested__chip"
            @click="emit('select', question)"
        >
            {{ question }}
        </button>
    </div>
</template>

<style scoped>
.suggested {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid rgba(217, 203, 182, 0.7);
}

.suggested__chip {
    border: 1px solid var(--niceno-gold-soft);
    border-radius: 9999px;
    background: rgba(255, 247, 232, 0.8);
    padding: 0.5rem 0.9rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #3d2314;
    transition: transform 150ms ease, border-color 150ms ease, background-color 150ms ease;
}

.suggested__chip:hover {
    transform: translateY(-1px);
    border-color: rgba(216, 154, 32, 0.62);
    background: #fff;
}

.suggested__chip:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .suggested__chip:hover { transform: none; }
}

@media (max-width: 560px) {
    .suggested {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .suggested::-webkit-scrollbar { display: none; }
    .suggested__chip { flex: 0 0 auto; white-space: nowrap; }
}
</style>
