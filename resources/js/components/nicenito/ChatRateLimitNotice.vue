<script setup>
defineProps({
    message: { type: String, default: '' },
});
</script>

<template>
    <div v-if="message" class="rate-limit" role="status">
        <span class="rate-limit__icon" aria-hidden="true">⏳</span>
        <p class="rate-limit__text">{{ message }}</p>
    </div>
</template>

<style scoped>
.rate-limit {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin: 0 1.25rem 0.5rem;
    padding: 0.65rem 0.9rem;
    border: 1px solid var(--niceno-gold-soft);
    border-radius: 0.75rem;
    background: rgba(255, 247, 232, 0.85);
}

.rate-limit__text {
    font-size: 0.85rem;
    line-height: 1.5;
    color: var(--niceno-burgundy);
}
</style>
