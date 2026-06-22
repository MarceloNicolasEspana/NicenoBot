const storageKey = 'nicenobot-catequesis-chat';
const nicenitoImages = {
    base: '/images/nicenito/clean/base.png',
    escuchando: '/images/nicenito/clean/escuchando.png',
    pensando: '/images/nicenito/clean/pensando.png',
    respondiendo: '/images/nicenito/clean/respondiendo.png',
    explicando: '/images/nicenito/clean/explicando.png',
    celebrando: '/images/nicenito/clean/celebrando.png',
    finalizando: '/images/nicenito/clean/finalizando.png',
};
const nicenitoAlt = {
    base: 'Nicenito esta en reposo',
    escuchando: 'Nicenito esta escuchando',
    pensando: 'Nicenito esta pensando',
    respondiendo: 'Nicenito esta respondiendo',
    explicando: 'Nicenito esta explicando',
    celebrando: 'Nicenito esta celebrando',
    finalizando: 'Nicenito esta finalizando la conversacion',
};
const nicenitoLabels = {
    base: 'Nicenito esta listo para acompanarte.',
    escuchando: 'Nicenito te esta escuchando con atencion.',
    pensando: 'Nicenito esta preparando una respuesta.',
    respondiendo: 'Nicenito ya esta respondiendo.',
    explicando: 'Nicenito esta explicando con mas detalle.',
    celebrando: 'Nicenito te anima a seguir adelante.',
    finalizando: 'Nicenito esta cerrando este momento contigo.',
};

const escapeHtml = (value) =>
    value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

const formatSources = (sources) => {
    if (!Array.isArray(sources) || sources.length === 0) {
        return '';
    }

    const items = sources
        .map(
            (source) =>
                `<li class="rounded-full bg-white/80 px-3 py-1">${escapeHtml(source.type)}: ${escapeHtml(source.reference)}</li>`,
        )
        .join('');

    return `<ul class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-slate-600">${items}</ul>`;
};

const createMessageMarkup = ({ role, content, sources = [] }) => {
    const isUser = role === 'user';
    const widthClass = isUser ? 'max-w-[84%] sm:max-w-[80%]' : 'max-w-[82%] sm:max-w-[78%]';
    const alignment = isUser ? 'ml-auto rounded-br-md bg-slate-950 text-white' : 'mr-auto rounded-bl-md bg-white text-slate-700 ring-1 ring-slate-200';
    const animationClass = isUser ? '' : ' chat-assistant-message';

    return `
        <article class="${widthClass} rounded-3xl px-5 py-4 text-sm leading-7 shadow-sm ${alignment}${animationClass}">
            <p>${escapeHtml(content)}</p>
            ${isUser ? '' : formatSources(sources)}
        </article>
    `;
};

const parseStoredMessages = () => {
    try {
        const raw = window.localStorage.getItem(storageKey);
        if (!raw) {
            return [];
        }

        const parsed = JSON.parse(raw);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const persistMessages = (messages) => {
    window.localStorage.setItem(storageKey, JSON.stringify(messages));
};

const initializeCatequesisChat = () => {
    const root = document.querySelector('#catequesis-chat');

    if (!root) {
        return;
    }

    const endpoint = root.dataset.endpoint;
    const form = document.querySelector('#chat-form');
    const textarea = document.querySelector('#chat-message');
    const submitButton = form?.querySelector('button[type="submit"]');
    const messagesContainer = document.querySelector('#chat-messages');
    const loadingIndicator = document.querySelector('#chat-loading');
    const errorBox = document.querySelector('#chat-error');
    const counter = document.querySelector('#chat-counter');
    const suggestedButtons = document.querySelectorAll('.suggested-question');
    const nicenitoAvatar = document.querySelector('#nicenito-avatar');
    const nicenitoAvatarImage = document.querySelector('#nicenito-avatar-image');
    const nicenitoAvatarLabel = document.querySelector('#nicenito-avatar-label');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (
        !form ||
        !textarea ||
        !submitButton ||
        !messagesContainer ||
        !loadingIndicator ||
        !errorBox ||
        !counter ||
        !endpoint ||
        !nicenitoAvatar ||
        !nicenitoAvatarImage ||
        !nicenitoAvatarLabel
    ) {
        return;
    }

    let history = parseStoredMessages();
    let isSending = false;
    let idleTimer;
    let avatarTransitionTimer;
    let responseStateTimer;

    const clearAvatarTimers = () => {
        window.clearTimeout(idleTimer);
        window.clearTimeout(avatarTransitionTimer);
        window.clearTimeout(responseStateTimer);
    };

    const scheduleReturnToBase = (delay = 4200) => {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(() => {
            if (!isSending && textarea.value.trim() === '') {
                setNicenitoState('base');
            }
        }, delay);
    };

    const setNicenitoState = (state) => {
        const nextState = nicenitoImages[state] ? state : 'base';

        nicenitoAvatar.dataset.state = nextState;
        nicenitoAvatar.classList.add('nicenito-avatar-changing');
        nicenitoAvatarImage.src = nicenitoImages[nextState];
        nicenitoAvatarImage.alt = nicenitoAlt[nextState] ?? nicenitoAlt.base;
        nicenitoAvatarLabel.textContent = nicenitoLabels[nextState] ?? nicenitoLabels.base;

        if (!prefersReducedMotion.matches) {
            window.clearTimeout(avatarTransitionTimer);
            avatarTransitionTimer = window.setTimeout(() => {
                nicenitoAvatar.classList.remove('nicenito-avatar-changing');
            }, 24);
        } else {
            nicenitoAvatar.classList.remove('nicenito-avatar-changing');
        }
    };

    const evaluateResponseState = ({ answer, sources }) => {
        const normalizedAnswer = answer.toLowerCase();
        const hasCelebration = ['muy bien', 'animo', 'sigue adelante', 'felicitaciones', 'felicidades', 'buen paso'].some((phrase) =>
            normalizedAnswer.includes(phrase),
        );
        const hasFinalizing = ['hasta pronto', 'que dios te bendiga', 'nos vemos', 'hasta luego', 'cuida tu corazon'].some((phrase) =>
            normalizedAnswer.includes(phrase),
        );
        const hasReflection = normalizedAnswer.includes('pregunta para reflexionar');
        const hasMultipleSections = answer.includes(':') || answer.includes('\n');
        const isExplanatory = answer.length > 350 || hasReflection || hasMultipleSections || (Array.isArray(sources) && sources.length > 0);

        if (hasFinalizing) {
            return 'finalizando';
        }

        if (hasCelebration) {
            return 'celebrando';
        }

        if (isExplanatory) {
            return 'explicando';
        }

        return 'base';
    };

    const syncCounter = () => {
        counter.textContent = String(textarea.value.length);
    };

    const scrollToBottom = () => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };

    const renderHistory = () => {
        messagesContainer.innerHTML = '';

        if (history.length === 0) {
            messagesContainer.innerHTML = `
                <article class="max-w-[82%] rounded-3xl rounded-bl-md bg-white px-5 py-4 text-sm leading-7 text-slate-700 shadow-sm ring-1 ring-slate-200 sm:max-w-[78%]">
                    <p>Hola, soy Nicenito. Puedes preguntarme sobre la fe, la oraci\u00f3n, Jes\u00fas, el Evangelio o los sacramentos.</p>
                </article>
            `;
            scrollToBottom();
            return;
        }

        messagesContainer.innerHTML = history.map(createMessageMarkup).join('');
        scrollToBottom();
    };

    const setLoading = (isLoading) => {
        isSending = isLoading;
        submitButton.disabled = isLoading;
        textarea.disabled = isLoading;
        loadingIndicator.classList.toggle('hidden', !isLoading);
    };

    const setError = (message = '') => {
        errorBox.textContent = message;
        errorBox.classList.toggle('hidden', message === '');
    };

    const appendMessage = (message) => {
        history.push(message);
        persistMessages(history);
        messagesContainer.insertAdjacentHTML('beforeend', createMessageMarkup(message));
        scrollToBottom();
    };

    const sendMessage = async (message) => {
        const trimmedMessage = message.trim();

        if (!trimmedMessage) {
            setError('Escribe una pregunta para continuar.');
            setNicenitoState('base');
            return;
        }

        if (trimmedMessage.length > 500) {
            setError('Tu mensaje no puede superar los 500 caracteres.');
            setNicenitoState('base');
            return;
        }

        clearAvatarTimers();
        setError();
        appendMessage({ role: 'user', content: trimmedMessage });
        textarea.value = '';
        syncCounter();
        setNicenitoState('pensando');
        setLoading(true);

        try {
            const response = await window.axios.post(endpoint, { message: trimmedMessage });
            const assistantMessage = {
                role: 'assistant',
                content: response.data.answer,
                sources: response.data.sources ?? [],
            };

            setNicenitoState('respondiendo');
            appendMessage(assistantMessage);

            responseStateTimer = window.setTimeout(() => {
                setNicenitoState(evaluateResponseState({
                    answer: assistantMessage.content,
                    sources: assistantMessage.sources,
                }));
                scheduleReturnToBase();
            }, prefersReducedMotion.matches ? 0 : 900);
        } catch (error) {
            history.pop();
            persistMessages(history);
            renderHistory();

            const validationMessage = error?.response?.data?.errors?.message?.[0];
            setError(validationMessage ?? 'Ocurri\u00f3 un problema al responder. Intenta de nuevo en unos segundos.');
            setNicenitoState('base');
        } finally {
            setLoading(false);
            textarea.focus();
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await sendMessage(textarea.value);
    });

    textarea.addEventListener('focus', () => {
        window.clearTimeout(responseStateTimer);
        window.clearTimeout(idleTimer);

        if (!isSending) {
            setNicenitoState('escuchando');
        }
    });

    textarea.addEventListener('blur', () => {
        if (!isSending) {
            scheduleReturnToBase(1200);
        }
    });

    textarea.addEventListener('input', () => {
        window.clearTimeout(responseStateTimer);
        window.clearTimeout(idleTimer);
        syncCounter();

        if (isSending) {
            return;
        }

        if (textarea.value.trim() === '') {
            setNicenitoState('base');
            return;
        }

        setNicenitoState('escuchando');
    });

    suggestedButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            window.clearTimeout(responseStateTimer);
            window.clearTimeout(idleTimer);
            textarea.value = button.dataset.question ?? '';
            syncCounter();
            setNicenitoState('escuchando');
            await sendMessage(textarea.value);
        });
    });

    renderHistory();
    syncCounter();
    setNicenitoState('base');
};

initializeCatequesisChat();
