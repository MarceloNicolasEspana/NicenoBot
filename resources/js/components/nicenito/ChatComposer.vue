<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    maxLength: { type: Number, default: 500 },
    disabled: { type: Boolean, default: false },
    brandName: { type: String, default: 'NicenoBot' },
});

const emit = defineEmits(['update:modelValue', 'submit', 'focus', 'blur']);

const textarea = ref(null);

const remaining = computed(() => props.modelValue.length);
const isEmpty = computed(() => props.modelValue.trim().length === 0);

const onInput = (event) => {
    emit('update:modelValue', event.target.value);
};

const submit = () => {
    if (props.disabled || isEmpty.value) return;
    emit('submit');
};

// Enter envía; Shift+Enter inserta salto de línea.
const onKeydown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submit();
    }
};

defineExpose({ focus: () => textarea.value?.focus() });
</script>

<template>
    <form class="composer" @submit.prevent="submit">
        <label for="nicenito-message" class="sr-only">Escribe tu pregunta</label>
        <div class="composer__row">
            <textarea
                id="nicenito-message"
                ref="textarea"
                class="composer__input"
                rows="2"
                :maxlength="maxLength"
                :value="modelValue"
                :placeholder="`Escribe tu pregunta a ${brandName}…`"
                :aria-label="`Escribe tu pregunta a ${brandName}`"
                @input="onInput"
                @keydown="onKeydown"
                @focus="emit('focus')"
                @blur="emit('blur')"
            ></textarea>
            <div class="composer__actions">
                <button
                    type="submit"
                    class="composer__send"
                    :disabled="disabled || isEmpty"
                    aria-label="Enviar pregunta"
                >
                    <span v-if="!disabled">Enviar</span>
                    <span v-else>Enviando…</span>
                </button>
                <p class="composer__counter" :class="{ 'is-near': remaining > maxLength - 40 }">
                    {{ remaining }}/{{ maxLength }}
                </p>
            </div>
        </div>
    </form>
</template>

<style scoped>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.composer {
    border-top: 1px solid rgba(217, 203, 182, 0.8);
    background: rgba(255, 255, 255, 0.75);
    padding: 1rem 1.25rem;
}

.composer__row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.composer__input {
    width: 100%;
    min-height: 5rem;
    resize: none;
    border: 1px solid var(--niceno-border);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.85);
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    line-height: 1.55;
    color: var(--niceno-ink);
    outline: none;
    transition: border-color 150ms ease, box-shadow 150ms ease;
}

.composer__input:focus {
    border-color: var(--niceno-gold);
    box-shadow: 0 0 0 4px var(--niceno-gold-soft);
}

.composer__actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.composer__send {
    min-width: 7rem;
    border-radius: 1rem;
    background: var(--niceno-burgundy);
    padding: 0.7rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 10px 20px rgba(91, 26, 31, 0.22);
    transition: background-color 150ms ease, transform 100ms ease;
}

.composer__send:hover:not(:disabled) {
    background: var(--niceno-burgundy-dark);
}

.composer__send:active:not(:disabled) {
    transform: translateY(1px);
}

.composer__send:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
}

.composer__send:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

.composer__counter {
    font-size: 0.75rem;
    color: var(--niceno-muted);
}

.composer__counter.is-near {
    color: var(--niceno-burgundy);
    font-weight: 700;
}

@media (min-width: 640px) {
    .composer__row {
        flex-direction: row;
        align-items: flex-end;
    }
    .composer__input { flex: 1; }
    .composer__actions {
        flex-direction: column-reverse;
        align-items: flex-end;
        gap: 0.4rem;
    }
}
</style>
