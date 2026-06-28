<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import {
    allImagePaths,
    altText,
    imageFor,
    normalizeState,
    statusText,
} from '../../utils/nicenitoState.js';

const props = defineProps({
    state: { type: String, default: 'base' },
    size: { type: String, default: 'normal' }, // compact | normal | large
    showStatusText: { type: Boolean, default: true },
    reducedMotion: { type: Boolean, default: false },
    basePath: { type: String, default: '/images/nicenito/clean/' },
});

const safeState = computed(() => normalizeState(props.state));
const src = ref(imageFor(safeState.value, props.basePath));
const changing = ref(false);

const alt = computed(() => altText(safeState.value));
const label = computed(() => statusText(safeState.value));

// Precargamos todos los estados una vez para evitar parpadeo al cambiar.
onMounted(() => {
    allImagePaths(props.basePath).forEach((path) => {
        const img = new Image();
        img.src = path;
    });
});

watch(safeState, (next) => {
    if (props.reducedMotion) {
        src.value = imageFor(next, props.basePath);
        return;
    }
    changing.value = true;
    // Breve fade de salida antes de cambiar la fuente.
    window.setTimeout(() => {
        src.value = imageFor(next, props.basePath);
        changing.value = false;
    }, 90);
});

// Fallback seguro: si una imagen de estado no carga, mostramos base.png.
const onError = (event) => {
    const fallback = imageFor('base', props.basePath);
    if (event.target.src !== window.location.origin + fallback && !event.target.src.endsWith(fallback)) {
        event.target.src = fallback;
    }
};
</script>

<template>
    <div
        class="nicenito-avatar"
        :class="[`nicenito-avatar--${size}`, `nicenito-avatar--state-${safeState}`, { 'is-changing': changing }]"
        :data-state="safeState"
    >
        <div class="nicenito-avatar__frame">
            <img
                class="nicenito-avatar__img"
                :src="src"
                :alt="alt"
                decoding="async"
                @error="onError"
            />
        </div>
        <div
            v-if="showStatusText"
            class="nicenito-avatar__status"
            aria-live="polite"
        >
            <span class="nicenito-avatar__status-text">{{ label }}</span>
            <span
                v-if="!reducedMotion"
                class="nicenito-avatar__dots"
                aria-hidden="true"
            >
                <span></span>
                <span></span>
                <span></span>
            </span>
        </div>
    </div>
</template>

<style scoped>
.nicenito-avatar {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

/* Marco de tamaño fijo: el contenido nunca debe saltar al cambiar de estado. */
.nicenito-avatar__frame {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    width: 100%;
    overflow: hidden;
}

.nicenito-avatar--compact .nicenito-avatar__frame {
    height: 3rem;
    width: 3rem;
    border-radius: 9999px;
    background: var(--niceno-cream);
    box-shadow: 0 4px 10px rgba(120, 89, 33, 0.14);
}

.nicenito-avatar--normal .nicenito-avatar__frame {
    height: 15rem;
}

.nicenito-avatar--large .nicenito-avatar__frame {
    height: 22rem;
}

.nicenito-avatar__img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    object-position: bottom center;
    transition: opacity 200ms ease, transform 200ms ease;
}

.nicenito-avatar--compact .nicenito-avatar__img {
    object-position: top center;
    padding: 0.15rem;
}

.is-changing .nicenito-avatar__img {
    opacity: 0.85;
    transform: translateY(3px);
}

.nicenito-avatar__status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    max-width: 22rem;
    padding: 0.55rem 1.1rem;
    border: 1px solid rgba(216, 154, 32, 0.22);
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.72);
    color: var(--niceno-ink);
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 10px 22px rgba(120, 89, 33, 0.1);
}

/* Tres puntos cálidos que flotan, simulando que NicenoBot escucha. */
.nicenito-avatar__dots {
    display: inline-flex;
    gap: 0.22rem;
}

.nicenito-avatar__dots span {
    width: 0.34rem;
    height: 0.34rem;
    border-radius: 9999px;
    background: var(--niceno-gold);
    opacity: 0.5;
    animation: nicenito-avatar-dots 1.15s ease-in-out infinite;
}

.nicenito-avatar__dots span:nth-child(2) {
    animation-delay: 140ms;
}

.nicenito-avatar__dots span:nth-child(3) {
    animation-delay: 280ms;
}

@keyframes nicenito-avatar-dots {
    0%, 100% {
        transform: translateY(0);
        opacity: 0.45;
    }
    50% {
        transform: translateY(-3px);
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .nicenito-avatar__img {
        transition: none;
        transform: none;
    }
    .nicenito-avatar__dots span {
        animation: none;
    }
}
</style>
