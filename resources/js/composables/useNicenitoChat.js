import { computed, ref } from 'vue';
import { normalizeState } from '../utils/nicenitoState.js';

// Tiempo máximo de espera de una respuesta antes de mostrar "está tardando".
const REQUEST_TIMEOUT_MS = 30000;
// Cuántos turnos previos enviamos como contexto al backend (sin fuentes).
const HISTORY_PAYLOAD_SIZE = 4;

let messageSeq = 0;
const nextId = () => `m${Date.now()}-${messageSeq++}`;

/**
 * Lógica de conversación de NicenoBot: envío de preguntas, CSRF, manejo de
 * estados de carga, historial local (solo memoria, nunca identidad) y traducción
 * de errores HTTP (429/419/422/500/timeout) a mensajes amables.
 *
 * Es deliberadamente agnóstico de la UI: expone estado reactivo y acciones.
 */
export function useNicenitoChat(config) {
    const {
        endpoint,
        quizEndpoint = '',
        accessUrl = '',
        maxLength = 500,
    } = config;

    const messages = ref([]); // { id, role: 'user'|'assistant', content, sources, reflection, needsHuman }
    const isSending = ref(false);
    const errorMessage = ref('');
    const rateLimit = ref(null); // { message } cuando el backend devuelve 429
    const quiz = ref(null); // { content_id, title, questions[] } al alcanzar el límite de ventana
    // Estado de Nicenito que proviene del backend tras una respuesta.
    const backendState = ref(null);
    // Última pregunta que falló, para poder reintentar sin perder el texto.
    const lastFailedMessage = ref('');

    const hasMessages = computed(() => messages.value.length > 0);

    const csrfToken = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const historyPayload = () =>
        messages.value
            .slice(-HISTORY_PAYLOAD_SIZE)
            .map(({ role, content }) => ({ role, content }));

    const clearError = () => {
        errorMessage.value = '';
        rateLimit.value = null;
    };

    const pushMessage = (message) => {
        const entry = { id: nextId(), sources: [], reflection: null, needsHuman: false, ...message };
        messages.value.push(entry);
        return entry;
    };

    const redirectToAccess = () => {
        if (accessUrl) {
            window.location.href = accessUrl;
            return true;
        }
        return false;
    };

    /**
     * Traduce un fallo de red/HTTP a un mensaje seguro. Nunca expone stacktraces
     * ni detalles internos de Gemini. Devuelve `true` si manejó una redirección.
     */
    const handleFailure = (status, data, { timedOut }) => {
        if (timedOut) {
            errorMessage.value =
                'NicenoBot está tardando más de lo esperado. Intenta nuevamente en unos momentos.';
            return;
        }

        // Sesión de participante perdida: volver al acceso para reingresar.
        if (status === 401) {
            if (redirectToAccess()) return;
        }

        // CSRF / sesión vencida: conservamos el texto y ofrecemos reintentar.
        if (status === 419) {
            errorMessage.value =
                'Tu sesión de seguridad expiró. Tu mensaje se conservó: puedes reintentar.';
            return;
        }

        if (status === 429) {
            rateLimit.value = {
                message:
                    data?.message ??
                    'Has hecho varias preguntas seguidas. Tómate un momento y vuelve en unos minutos.',
            };
            // Límite de ventana con quiz disponible: lo mostramos en un modal.
            if (data?.reason === 'rate_window' && data?.quiz?.questions?.length) {
                quiz.value = data.quiz;
            }
            return;
        }

        if (status === 422) {
            errorMessage.value =
                data?.errors?.message?.[0] ??
                data?.message ??
                'Revisa tu pregunta e intenta de nuevo.';
            return;
        }

        // 500 u otros: mensaje genérico, sin detalles del servidor.
        errorMessage.value =
            'Ocurrió un problema al responder. Intenta de nuevo en unos segundos.';
    };

    /**
     * Envía una pregunta al backend. Devuelve el estado final de Nicenito para
     * que la página coordine la animación del avatar, o `null` si falló.
     */
    const send = async (rawMessage) => {
        const message = (rawMessage ?? '').trim();

        if (!message) {
            errorMessage.value = 'Escribe una pregunta para continuar.';
            return null;
        }
        if (message.length > maxLength) {
            errorMessage.value = `Tu mensaje no puede superar los ${maxLength} caracteres.`;
            return null;
        }
        if (isSending.value) {
            return null; // Evita doble envío mientras hay una solicitud en curso.
        }

        clearError();
        lastFailedMessage.value = '';

        const history = historyPayload();
        pushMessage({ role: 'user', content: message });
        isSending.value = true;
        backendState.value = null;

        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);
        let timedOut = false;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message, history }),
                signal: controller.signal,
            });

            if (!response.ok) {
                let data = null;
                try {
                    data = await response.json();
                } catch {
                    // Respuesta sin cuerpo JSON; seguimos con el mensaje genérico.
                }
                // El mensaje del usuario se conserva en el composer para reintento.
                messages.value.pop();
                lastFailedMessage.value = message;
                handleFailure(response.status, data, { timedOut: false });
                return null;
            }

            const data = await response.json();
            const state = normalizeState(data.nicenito_state);

            pushMessage({
                role: 'assistant',
                content: typeof data.answer === 'string' ? data.answer : '',
                sources: Array.isArray(data.sources) ? data.sources : [],
                reflection: typeof data.reflection === 'string' ? data.reflection : null,
                needsHuman: data.needs_human_guidance === true,
            });

            backendState.value = state;
            return state;
        } catch (error) {
            timedOut = error?.name === 'AbortError';
            messages.value.pop();
            lastFailedMessage.value = message;
            handleFailure(null, null, { timedOut });
            return null;
        } finally {
            window.clearTimeout(timeout);
            isSending.value = false;
        }
    };

    /**
     * Envía las respuestas del quiz al backend (que corrige y guarda el intento)
     * y devuelve { results, score, total }, o null si falló.
     *
     * @param  {Array<number|null>} answers  Índice elegido por pregunta.
     */
    const submitQuiz = async (answers) => {
        if (!quizEndpoint) {
            return null;
        }

        try {
            const response = await fetch(quizEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ answers }),
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch {
            return null;
        }
    };

    const closeQuiz = () => {
        quiz.value = null;
    };

    return {
        messages,
        isSending,
        errorMessage,
        rateLimit,
        quiz,
        backendState,
        lastFailedMessage,
        hasMessages,
        clearError,
        send,
        submitQuiz,
        closeQuiz,
    };
}
