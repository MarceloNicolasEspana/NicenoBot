<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import NicenitoAvatar from './NicenitoAvatar.vue';

const props = defineProps({
    quiz: { type: Object, required: true }, // { title, questions: [{ index, question, options[] }] }
    submit: { type: Function, required: true }, // async (answers) => { results, score, total }
    basePath: { type: String, default: '/images/nicenito/clean/' },
    reducedMotion: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const questions = computed(() => props.quiz?.questions ?? []);
const selected = ref(questions.value.map(() => null));
const phase = ref('answering'); // answering | submitting | results
const results = ref(null); // [{ question_index, selected_index, correct_index, is_correct }]
const score = ref(0);
const errorMessage = ref('');

const dialog = ref(null);

const avatarState = computed(() => (phase.value === 'results' ? 'celebrando' : 'explicando'));
const allAnswered = computed(() => selected.value.every((value) => value !== null));

const choose = (questionIndex, optionIndex) => {
    if (phase.value !== 'answering') return;
    selected.value[questionIndex] = optionIndex;
};

const resultFor = (questionIndex) =>
    results.value?.find((r) => r.question_index === questionIndex) ?? null;

const optionClass = (questionIndex, optionIndex) => {
    if (phase.value !== 'results') {
        return selected.value[questionIndex] === optionIndex ? 'is-selected' : '';
    }
    const result = resultFor(questionIndex);
    if (!result) return '';
    if (optionIndex === result.correct_index) return 'is-correct';
    if (optionIndex === result.selected_index) return 'is-wrong';
    return '';
};

const send = async () => {
    if (!allAnswered.value || phase.value !== 'answering') return;
    phase.value = 'submitting';
    errorMessage.value = '';

    const response = await props.submit(selected.value.map((v) => (v === null ? null : v)));

    if (!response) {
        errorMessage.value = 'No pudimos enviar tus respuestas. Intenta de nuevo.';
        phase.value = 'answering';
        return;
    }

    results.value = response.results ?? [];
    score.value = response.score ?? 0;
    phase.value = 'results';
};

const close = () => emit('close');

const onKeydown = (event) => {
    if (event.key === 'Escape') close();
};

onMounted(() => {
    document.addEventListener('keydown', onKeydown);
    dialog.value?.focus();
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div class="quiz-overlay" @click.self="close">
        <div
            ref="dialog"
            class="quiz-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="quiz-title"
            tabindex="-1"
        >
            <header class="quiz-modal__header">
                <NicenitoAvatar
                    :state="avatarState"
                    size="compact"
                    :show-status-text="false"
                    :reduced-motion="reducedMotion"
                    :base-path="basePath"
                />
                <div class="quiz-modal__heading">
                    <h2 id="quiz-title" class="quiz-modal__title">Mientras esperas, un repaso</h2>
                    <p class="quiz-modal__subtitle">{{ quiz.title }}</p>
                </div>
                <button type="button" class="quiz-modal__close" aria-label="Cerrar" @click="close">&times;</button>
            </header>

            <div class="quiz-modal__body">
                <p v-if="phase === 'results'" class="quiz-modal__score">
                    Respondiste bien <strong>{{ score }}</strong> de <strong>{{ questions.length }}</strong>.
                </p>

                <ol class="quiz-modal__list">
                    <li v-for="(q, qi) in questions" :key="qi" class="quiz-q">
                        <p class="quiz-q__text">{{ q.question }}</p>
                        <div class="quiz-q__options">
                            <button
                                v-for="(option, oi) in q.options"
                                :key="oi"
                                type="button"
                                class="quiz-q__option"
                                :class="optionClass(qi, oi)"
                                :disabled="phase !== 'answering'"
                                :aria-pressed="selected[qi] === oi"
                                @click="choose(qi, oi)"
                            >
                                {{ option }}
                            </button>
                        </div>
                    </li>
                </ol>

                <p v-if="errorMessage" class="quiz-modal__error" role="alert">{{ errorMessage }}</p>
            </div>

            <footer class="quiz-modal__footer">
                <button
                    v-if="phase !== 'results'"
                    type="button"
                    class="quiz-modal__send"
                    :disabled="!allAnswered || phase === 'submitting'"
                    @click="send"
                >
                    {{ phase === 'submitting' ? 'Enviando…' : 'Enviar respuestas' }}
                </button>
                <button v-else type="button" class="quiz-modal__send" @click="close">Volver al chat</button>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.quiz-overlay {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(36, 28, 22, 0.55);
    backdrop-filter: blur(2px);
}

.quiz-modal {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 34rem;
    max-height: 90vh;
    overflow: hidden;
    border-radius: 1.25rem;
    background: var(--niceno-cream, #fff7e8);
    box-shadow: 0 24px 60px rgba(36, 28, 22, 0.35);
    outline: none;
}

.quiz-modal__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(217, 203, 182, 0.7);
    background: rgba(255, 255, 255, 0.6);
}

.quiz-modal__heading {
    flex: 1;
    min-width: 0;
}

.quiz-modal__title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--niceno-ink);
}

.quiz-modal__subtitle {
    font-size: 0.85rem;
    color: var(--niceno-muted);
}

.quiz-modal__close {
    flex-shrink: 0;
    font-size: 1.5rem;
    line-height: 1;
    color: var(--niceno-muted);
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
}

.quiz-modal__close:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

.quiz-modal__body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 1.1rem 1.25rem;
}

.quiz-modal__score {
    margin-bottom: 0.9rem;
    padding: 0.6rem 0.9rem;
    border-radius: 0.75rem;
    background: rgba(216, 154, 32, 0.14);
    font-size: 0.95rem;
    color: var(--niceno-ink);
}

.quiz-modal__list {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    list-style: none;
    counter-reset: quiz;
}

.quiz-q__text {
    margin-bottom: 0.55rem;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.5;
    color: var(--niceno-ink);
}

.quiz-q__text::before {
    counter-increment: quiz;
    content: counter(quiz) '. ';
    color: var(--niceno-burgundy);
    font-weight: 700;
}

.quiz-q__options {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.quiz-q__option {
    text-align: left;
    padding: 0.6rem 0.85rem;
    border: 1px solid var(--niceno-border);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
    color: var(--niceno-ink);
    transition: border-color 120ms ease, background-color 120ms ease;
}

.quiz-q__option:not(:disabled):hover {
    border-color: var(--niceno-gold);
}

.quiz-q__option:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

.quiz-q__option.is-selected {
    border-color: var(--niceno-burgundy);
    background: rgba(111, 29, 37, 0.08);
    font-weight: 600;
}

.quiz-q__option.is-correct {
    border-color: #15803d;
    background: #dcfce7;
    color: #14532d;
    font-weight: 700;
}

.quiz-q__option.is-wrong {
    border-color: #b91c1c;
    background: #fee2e2;
    color: #7f1d1d;
}

.quiz-modal__error {
    margin-top: 0.75rem;
    color: #b91c1c;
    font-size: 0.85rem;
    font-weight: 600;
}

.quiz-modal__footer {
    display: flex;
    justify-content: flex-end;
    padding: 0.9rem 1.25rem;
    border-top: 1px solid rgba(217, 203, 182, 0.7);
    background: rgba(255, 255, 255, 0.6);
}

.quiz-modal__send {
    min-width: 9rem;
    padding: 0.7rem 1.25rem;
    border-radius: 0.9rem;
    background: var(--niceno-burgundy);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    transition: background-color 150ms ease;
}

.quiz-modal__send:not(:disabled):hover {
    background: var(--niceno-burgundy-dark);
}

.quiz-modal__send:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

.quiz-modal__send:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

@media (max-width: 560px) {
    .quiz-overlay { padding: 0; }
    .quiz-modal {
        max-width: none;
        max-height: 100dvh;
        height: 100dvh;
        border-radius: 0;
    }
}
</style>
