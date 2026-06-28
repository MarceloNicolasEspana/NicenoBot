<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import NicenitoAvatar from '../components/nicenito/NicenitoAvatar.vue';
import ChatHeader from '../components/nicenito/ChatHeader.vue';
import ChatMessageList from '../components/nicenito/ChatMessageList.vue';
import SuggestedQuestions from '../components/nicenito/SuggestedQuestions.vue';
import ChatComposer from '../components/nicenito/ChatComposer.vue';
import ChatRateLimitNotice from '../components/nicenito/ChatRateLimitNotice.vue';
import QuizModal from '../components/nicenito/QuizModal.vue';
import { useNicenitoChat } from '../composables/useNicenitoChat.js';

const props = defineProps({
    displayName: { type: String, default: '' },
    endpoint: { type: String, required: true },
    quizUrl: { type: String, default: '' },
    accessUrl: { type: String, default: '' },
    logoutUrl: { type: String, default: '' },
    maxLength: { type: Number, default: 500 },
    suggestedQuestions: { type: Array, default: () => [] },
    avatarBasePath: { type: String, default: '/images/nicenito/clean/' },
    brandName: { type: String, default: 'NicenoBot' },
    csrfToken: { type: String, default: '' },
});

const reducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const chat = useNicenitoChat({
    endpoint: props.endpoint,
    quizEndpoint: props.quizUrl,
    accessUrl: props.accessUrl,
    maxLength: props.maxLength,
});

const draft = ref('');
const composer = ref(null);

// --- Estado del avatar -----------------------------------------------------
// El frontend solo origina estados transitorios (escuchando/pensando). El
// estado final lo dicta el backend. Tras inactividad volvemos a `base`.
const avatarState = ref('base');
let idleTimer;
let responseTimer;

const clearTimers = () => {
    window.clearTimeout(idleTimer);
    window.clearTimeout(responseTimer);
};

const scheduleReturnToBase = (delay = 4500) => {
    window.clearTimeout(idleTimer);
    idleTimer = window.setTimeout(() => {
        if (!chat.isSending.value && draft.value.trim() === '') {
            avatarState.value = 'base';
        }
    }, delay);
};

const setAvatar = (state) => {
    clearTimers();
    avatarState.value = state;
};

// --- Sugerencias -----------------------------------------------------------
// Se ocultan cuando la conversación ya tiene varios mensajes.
const showSuggestions = computed(() => chat.messages.value.length < 2);

const onSuggestion = (question) => {
    draft.value = question;
    composer.value?.focus();
    if (!reducedMotion) avatarState.value = 'escuchando';
};

// --- Composer --------------------------------------------------------------
const onFocus = () => {
    if (chat.isSending.value || reducedMotion) return;
    setAvatar('escuchando');
};

const onBlur = () => {
    if (!chat.isSending.value) scheduleReturnToBase(1500);
};

const submit = async () => {
    const text = draft.value;
    if (!text.trim() || chat.isSending.value) return;

    setAvatar('pensando');
    const accepted = (() => {
        // Vaciamos el textarea solo si pasa la validación local mínima.
        if (text.trim().length === 0 || text.trim().length > props.maxLength) {
            return false;
        }
        draft.value = '';
        return true;
    })();

    const finalState = await chat.send(text);

    if (finalState === null) {
        // Falló: restauramos el texto si lo habíamos vaciado, para reintentar.
        if (accepted && chat.lastFailedMessage.value) {
            draft.value = chat.lastFailedMessage.value;
        }
        setAvatar('base');
        return;
    }

    // Respuesta recibida: breve "respondiendo" y luego el estado del backend.
    avatarState.value = 'respondiendo';
    responseTimer = window.setTimeout(
        () => {
            avatarState.value = finalState;
            scheduleReturnToBase();
        },
        reducedMotion ? 0 : 800,
    );
};

const retry = () => {
    if (chat.lastFailedMessage.value) {
        draft.value = chat.lastFailedMessage.value;
    }
    chat.clearError();
    composer.value?.focus();
};

onBeforeUnmount(clearTimers);
</script>

<template>
    <div class="nicenito-page">
        <div class="nicenito-page__inner">
            <!-- Columna de bienvenida / avatar -->
            <aside class="nicenito-page__welcome">
                <div class="nicenito-page__welcome-inner">
                    <h1 class="nicenito-page__title">Pregúntale a {{ brandName }}</h1>
                    <p class="nicenito-page__lead">
                        Conversa sobre el Evangelio, la fe, la oración y los sacramentos.
                    </p>
                    <NicenitoAvatar
                        :state="avatarState"
                        size="large"
                        :show-status-text="!reducedMotion"
                        :reduced-motion="reducedMotion"
                        :base-path="avatarBasePath"
                        class="nicenito-page__welcome-avatar"
                    />
                </div>
            </aside>

            <!-- Columna de conversación -->
            <section class="nicenito-page__chat">
                <ChatHeader
                    :brand-name="brandName"
                    :is-sending="chat.isSending.value"
                    :base-path="avatarBasePath"
                    :logout-url="logoutUrl"
                    :csrf-token="csrfToken"
                />

                <!-- Avatar compacto solo para móvil (la columna izquierda se oculta) -->
                <div class="nicenito-page__mobile-avatar">
                    <NicenitoAvatar
                        :state="avatarState"
                        size="normal"
                        :show-status-text="!reducedMotion"
                        :reduced-motion="reducedMotion"
                        :base-path="avatarBasePath"
                    />
                </div>

                <SuggestedQuestions
                    v-if="showSuggestions"
                    :questions="suggestedQuestions"
                    @select="onSuggestion"
                />

                <ChatMessageList
                    :messages="chat.messages.value"
                    :is-sending="chat.isSending.value"
                    :brand-name="brandName"
                    :display-name="displayName"
                    :base-path="avatarBasePath"
                    :reduced-motion="reducedMotion"
                />

                <ChatRateLimitNotice
                    v-if="chat.rateLimit.value"
                    :message="chat.rateLimit.value.message"
                />

                <div v-if="chat.errorMessage.value" class="nicenito-page__error" role="alert">
                    <p>{{ chat.errorMessage.value }}</p>
                    <button type="button" class="nicenito-page__retry" @click="retry">
                        Reintentar
                    </button>
                </div>

                <ChatComposer
                    ref="composer"
                    v-model="draft"
                    :max-length="maxLength"
                    :disabled="chat.isSending.value"
                    :brand-name="brandName"
                    @submit="submit"
                    @focus="onFocus"
                    @blur="onBlur"
                />

                <p class="nicenito-page__disclaimer">
                    Este chatbot es una ayuda para aprender y reflexionar. No reemplaza la
                    conversación con tu catequista, sacerdote o adulto responsable.
                </p>
            </section>
        </div>

        <QuizModal
            v-if="chat.quiz.value"
            :quiz="chat.quiz.value"
            :submit="chat.submitQuiz"
            :base-path="avatarBasePath"
            :reduced-motion="reducedMotion"
            @close="chat.closeQuiz()"
        />
    </div>
</template>

<style scoped>
.nicenito-page {
    min-height: 100vh;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-x: hidden;
}

.nicenito-page__inner {
    display: grid;
    /* minmax(0, …) permite que la columna se encoja bajo el ancho de su
       contenido; con `1fr` el contenido se recortaba a la derecha en móvil. */
    grid-template-columns: minmax(0, 1fr);
    width: 100%;
    max-width: 1280px;
    height: min(52rem, calc(100vh - 2.5rem));
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.42);
    border-radius: 2rem;
    background: rgba(255, 251, 242, 0.74);
    box-shadow: var(--niceno-shadow);
    backdrop-filter: blur(18px);
}

.nicenito-page__welcome {
    display: none;
}

.nicenito-page__welcome-inner {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    height: 100%;
    padding: 2rem;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 10%, rgba(216, 154, 32, 0.22), transparent 38%),
        linear-gradient(180deg, rgba(255, 247, 232, 0.85), rgba(247, 235, 215, 0.8));
}

/* Fondo de interior de catedral, desenfocado, SOLO en el panel de NicenoBot.
   Va detrás del contenido, que permanece nítido. La capa cálida superior lo
   integra con la paleta del sitio. */
.nicenito-page__welcome-inner::before {
    content: '';
    position: absolute;
    inset: -7%;
    z-index: 0;
    background:
        linear-gradient(180deg, rgba(255, 247, 232, 0.42), rgba(247, 235, 215, 0.52)),
        url('/images/nicenito/cathedral.svg') center / cover no-repeat;
    filter: blur(7px) saturate(1.04);
    transform: scale(1.06);
    pointer-events: none;
}

/* El contenido real (título, texto, NicenoBot) por encima del fondo. */
.nicenito-page__welcome-inner > * {
    position: relative;
    z-index: 1;
}

/* Glow dorado cálido tras NicenoBot. */
.nicenito-page__welcome-inner::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: 4rem;
    width: 20rem;
    height: 20rem;
    transform: translateX(-50%);
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(216, 154, 32, 0.2), transparent 62%);
    pointer-events: none;
    z-index: 0;
}

/* NicenoBot baja al pie de la columna, a la altura del chat. */
.nicenito-page__welcome-avatar {
    position: relative;
    z-index: 1;
    margin-top: auto;
}

.nicenito-page__title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--niceno-ink);
}

.nicenito-page__lead {
    max-width: 26rem;
    font-size: 1rem;
    line-height: 1.6;
    color: var(--niceno-muted);
}

.nicenito-page__chat {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    background: rgba(255, 255, 255, 0.85);
}

/* Hero de NicenoBot para una sola columna (tablet/móvil). Contenido y avatar
   no deben desbordar ni provocar scroll horizontal. */
.nicenito-page__mobile-avatar {
    display: flex;
    flex: 0 0 auto; /* alto fijo, independiente de la altura del chat */
    justify-content: center;
    padding: 0.75rem 1rem 0.25rem;
    border-bottom: 1px solid rgba(217, 203, 182, 0.6);
    background: radial-gradient(circle at 50% 0%, rgba(216, 154, 32, 0.16), transparent 62%);
}

.nicenito-page__mobile-avatar :deep(.nicenito-avatar--normal .nicenito-avatar__frame) {
    height: 11rem;
}

.nicenito-page__error {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin: 0 1.25rem 0.5rem;
    padding: 0.65rem 0.9rem;
    border: 1px solid #fecaca;
    border-radius: 0.75rem;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 0.85rem;
    font-weight: 600;
}

.nicenito-page__retry {
    flex-shrink: 0;
    border: 1px solid currentColor;
    border-radius: 9999px;
    padding: 0.3rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
}

.nicenito-page__retry:focus-visible {
    outline: 2px solid var(--niceno-gold);
    outline-offset: 2px;
}

.nicenito-page__disclaimer {
    padding: 0.9rem 1.25rem;
    background: var(--niceno-ink);
    color: rgba(255, 255, 255, 0.92);
    font-size: 0.82rem;
    line-height: 1.5;
}

/* Escritorio: dos columnas equilibradas. */
@media (min-width: 1024px) {
    .nicenito-page__inner {
        grid-template-columns: minmax(0, 0.42fr) minmax(0, 0.58fr);
    }

    .nicenito-page__welcome {
        display: block;
        border-right: 1px solid rgba(217, 203, 182, 0.64);
    }

    .nicenito-page__mobile-avatar {
        display: none;
    }
}

@media (max-width: 1023px) {
    .nicenito-page__inner {
        height: min(46rem, calc(100vh - 2rem));
    }
}

@media (max-width: 560px) {
    .nicenito-page {
        padding: 0;
    }
    .nicenito-page__inner {
        height: 100vh; /* respaldo para navegadores sin dvh */
        height: 100dvh;
        max-width: none;
        border: 0;
        border-radius: 0;
        backdrop-filter: none;
    }

    /* Hero de celular: solo la mitad superior de NicenoBot, más grande para
       apreciar su expresión, con degradado inferior que funde hacia el chat. */
    .nicenito-page__mobile-avatar {
        position: relative;
        /* Alto fijo: no debe encogerse cuando el chat crece. */
        flex: 0 0 auto;
        padding: 0.5rem 0.75rem 0;
        overflow: hidden;
    }
    /* Mismo fondo de catedral (desenfocado) que en escritorio, SOLO detrás del
       hero de NicenoBot. `overflow: hidden` lo confina a esta zona y el degradado
       inferior lo funde con el chat. */
    .nicenito-page__mobile-avatar::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background:
            linear-gradient(180deg, rgba(255, 247, 232, 0.42), rgba(247, 235, 215, 0.55)),
            url('/images/nicenito/cathedral.svg') center / cover no-repeat;
        filter: blur(7px) saturate(1.04);
        transform: scale(1.06);
        pointer-events: none;
        -webkit-mask-image: linear-gradient(to bottom, #000 68%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 68%, transparent 100%);
    }
    .nicenito-page__mobile-avatar :deep(.nicenito-avatar) {
        position: relative;
        z-index: 1;
        gap: 0.2rem;
        width: 100%;
    }
    .nicenito-page__mobile-avatar :deep(.nicenito-avatar--normal .nicenito-avatar__frame) {
        height: 10rem;
        align-items: flex-start;
        -webkit-mask-image: linear-gradient(to bottom, #000 60%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 60%, transparent 100%);
    }
    .nicenito-page__mobile-avatar :deep(.nicenito-avatar__img) {
        width: 14rem;
        max-width: none;
        max-height: none;
        height: auto;
        object-position: top center;
    }
    .nicenito-page__mobile-avatar :deep(.nicenito-avatar__status) {
        margin-top: -0.5rem;
        padding: 0.4rem 0.85rem;
        font-size: 0.8rem;
    }

    .nicenito-page__error {
        margin: 0 0.75rem 0.5rem;
    }

    .nicenito-page__disclaimer {
        display: none;
    }
}
</style>
