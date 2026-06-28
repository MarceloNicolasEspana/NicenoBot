<script setup>
import NicenitoAvatar from './NicenitoAvatar.vue';

defineProps({
    brandName: { type: String, default: 'NicenoBot' },
    displayName: { type: String, default: '' },
    basePath: { type: String, default: '/images/nicenito/clean/' },
});

const greeting = (brand, name) => {
    const who = name && name !== 'Tú' ? `Hola, ${name}. ` : 'Hola. ';
    return `${who}Soy ${brand}. Podemos conversar sobre el Evangelio, la fe, la oración y la catequesis.`;
};
</script>

<template>
    <article class="chat-empty">
        <NicenitoAvatar
            state="base"
            size="compact"
            :show-status-text="false"
            :base-path="basePath"
            class="chat-empty__avatar"
        />
        <div class="chat-empty__bubble">
            <p>{{ greeting(brandName, displayName) }}</p>
        </div>
    </article>
</template>

<style scoped>
.chat-empty {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
}

.chat-empty__avatar {
    flex-shrink: 0;
}

.chat-empty__bubble {
    max-width: min(82%, 40rem);
    padding: 0.9rem 1.1rem;
    border: 1px solid rgba(217, 203, 182, 0.75);
    border-radius: 1.25rem;
    border-top-left-radius: 0.4rem;
    background: rgba(255, 247, 232, 0.9);
    color: #233044;
    font-size: 0.95rem;
    line-height: 1.65;
    box-shadow: 0 8px 20px rgba(17, 24, 39, 0.07);
}
</style>
