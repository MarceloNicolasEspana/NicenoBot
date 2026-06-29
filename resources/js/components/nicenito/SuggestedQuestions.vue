<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    questions: { type: Array, default: () => [] },
});

// Al elegir una sugerencia, la página decide si la copia al input o la envía.
// Las sugerencias NO llaman a Gemini por sí mismas.
const emit = defineEmits(['select']);

const viewport = ref(null);
const canScrollBack = ref(false);
const canScrollForward = ref(false);
const isDragging = ref(false);

let resizeObserver;
let dragStartX = 0;
let dragStartScrollLeft = 0;
let activePointerId = null;
let pointerMoved = false;
let suppressNextClick = false;

const updateNavigation = () => {
    const element = viewport.value;
    if (!element) return;

    const maxScrollLeft = Math.max(0, element.scrollWidth - element.clientWidth);
    canScrollBack.value = element.scrollLeft > 2;
    canScrollForward.value = element.scrollLeft < maxScrollLeft - 2;
};

const slide = (direction) => {
    const element = viewport.value;
    if (!element) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    element.scrollBy({
        left: direction * Math.max(element.clientWidth * 0.8, 180),
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
    });
};

// El gesto tactil usa el desplazamiento nativo del navegador. Para mouse se
// agrega arrastre explicito, porque no todos los navegadores permiten tomar y
// mover horizontalmente una zona con overflow.
const startMouseDrag = (event) => {
    const isMobileCarousel = window.matchMedia('(max-width: 560px)').matches;
    if (!isMobileCarousel || event.pointerType !== 'mouse' || event.button !== 0 || !viewport.value) return;

    dragStartX = event.clientX;
    dragStartScrollLeft = viewport.value.scrollLeft;
    activePointerId = event.pointerId;
    pointerMoved = false;
};

const moveMouseDrag = (event) => {
    if (event.pointerType !== 'mouse' || activePointerId !== event.pointerId || !viewport.value) return;

    const distance = event.clientX - dragStartX;
    if (Math.abs(distance) > 4) {
        pointerMoved = true;
        isDragging.value = true;
        if (!viewport.value.hasPointerCapture(event.pointerId)) {
            viewport.value.setPointerCapture(event.pointerId);
        }
        event.preventDefault();
    }

    if (pointerMoved) viewport.value.scrollLeft = dragStartScrollLeft - distance;
};

const finishMouseDrag = (event) => {
    const element = viewport.value;
    if (!element || event.pointerType !== 'mouse' || activePointerId !== event.pointerId) return;

    if (element.hasPointerCapture(event.pointerId)) element.releasePointerCapture(event.pointerId);
    if (pointerMoved) {
        suppressNextClick = true;
        window.setTimeout(() => { suppressNextClick = false; }, 0);
    }

    isDragging.value = false;
    activePointerId = null;
    updateNavigation();
};

const selectQuestion = (question) => {
    if (!suppressNextClick) emit('select', question);
};

const refreshAfterRender = async () => {
    await nextTick();
    if (resizeObserver && viewport.value) {
        resizeObserver.disconnect();
        resizeObserver.observe(viewport.value);
    }
    updateNavigation();
};

onMounted(() => {
    if ('ResizeObserver' in window) {
        resizeObserver = new ResizeObserver(updateNavigation);
    }
    refreshAfterRender();
    window.addEventListener('resize', updateNavigation);
});

watch(() => props.questions.length, refreshAfterRender);

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    window.removeEventListener('resize', updateNavigation);
});
</script>

<template>
    <div v-if="questions.length" class="suggested" role="region" aria-label="Preguntas sugeridas">
        <button
            type="button"
            class="suggested__control suggested__control--back"
            :disabled="!canScrollBack"
            aria-label="Ver preguntas anteriores"
            @click="slide(-1)"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </button>

        <div
            ref="viewport"
            class="suggested__viewport"
            :class="{ 'is-dragging': isDragging }"
            @scroll.passive="updateNavigation"
            @pointerdown="startMouseDrag"
            @pointermove="moveMouseDrag"
            @pointerup="finishMouseDrag"
            @pointercancel="finishMouseDrag"
            @dragstart.prevent
        >
            <button
                v-for="(question, index) in questions"
                :key="index"
                type="button"
                class="suggested__chip"
                @click="selectQuestion(question)"
            >
                {{ question }}
            </button>
        </div>

        <button
            type="button"
            class="suggested__control suggested__control--forward"
            :disabled="!canScrollForward"
            aria-label="Ver más preguntas"
            @click="slide(1)"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m9 18 6-6-6-6" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.suggested {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid rgba(217, 203, 182, 0.7);
}

.suggested__viewport {
    display: flex;
    flex: 1 1 auto;
    flex-wrap: wrap;
    min-width: 0;
    gap: 0.6rem;
}

.suggested__control {
    display: none;
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

.suggested__chip:focus-visible,
.suggested__control:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .suggested__chip:hover { transform: none; }
}

@media (max-width: 560px) {
    .suggested {
        gap: 0.3rem;
        padding: 0.55rem 0.4rem;
    }

    .suggested__viewport {
        flex-wrap: nowrap;
        gap: 0.5rem;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
        touch-action: pan-x;
        cursor: grab;
    }

    .suggested__viewport.is-dragging {
        scroll-snap-type: none;
        cursor: grabbing;
        user-select: none;
    }

    .suggested__viewport::-webkit-scrollbar { display: none; }

    .suggested__control {
        display: inline-flex;
        flex: 0 0 2rem;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border: 1px solid rgba(216, 154, 32, 0.45);
        border-radius: 9999px;
        background: #fffaf0;
        color: #5b1a1f;
        box-shadow: 0 3px 10px rgba(91, 26, 31, 0.1);
        transition: opacity 150ms ease, background-color 150ms ease;
    }

    .suggested__control:disabled {
        opacity: 0.28;
        cursor: default;
    }

    .suggested__control:not(:disabled):active {
        background: #f9e9c9;
    }

    .suggested__control svg {
        width: 1.1rem;
        height: 1.1rem;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.25;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .suggested__chip {
        flex: 0 0 auto;
        scroll-snap-align: start;
        white-space: nowrap;
    }
}
</style>
