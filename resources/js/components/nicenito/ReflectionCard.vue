<script setup>
defineProps({
    reflection: { type: String, default: '' },
});
</script>

<template>
    <aside v-if="reflection" class="reflection-card">
        <p class="reflection-card__eyebrow">Para reflexionar</p>
        <p class="reflection-card__text">{{ reflection }}</p>
    </aside>
</template>

<style scoped>
/* Sobrio, sin aire de tarea obligatoria ni evaluación. */
.reflection-card {
    margin-top: 0.75rem;
    padding: 0.75rem 0.9rem;
    border-left: 3px solid var(--niceno-gold);
    border-radius: 0.5rem;
    background: rgba(255, 247, 232, 0.7);
}

.reflection-card__eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--niceno-gold);
}

.reflection-card__text {
    margin-top: 0.2rem;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--niceno-ink);
}
</style>
