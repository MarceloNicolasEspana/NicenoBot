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
    base: 'NicenoBot esta en reposo',
    escuchando: 'NicenoBot esta escuchando',
    pensando: 'NicenoBot esta pensando',
    respondiendo: 'NicenoBot esta respondiendo',
    explicando: 'NicenoBot esta explicando',
    celebrando: 'NicenoBot esta celebrando',
    finalizando: 'NicenoBot esta finalizando la conversacion',
};
const nicenitoLabels = {
    base: 'NicenoBot está listo para ayudarte.',
    escuchando: 'NicenoBot está escuchando.',
    pensando: 'NicenoBot está pensando.',
    respondiendo: 'NicenoBot está respondiendo.',
    explicando: 'NicenoBot está explicando.',
    celebrando: 'NicenoBot te anima a seguir adelante.',
    finalizando: 'NicenoBot está cerrando este momento contigo.',
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
    const animationClass = isUser ? '' : ' chat-assistant-message';

    if (isUser) {
        return `
            <article class="chat-row chat-row-user">
                <div class="chat-bubble chat-bubble-user">
                    <p>${escapeHtml(content)}</p>
                </div>
            </article>
        `;
    }

    return `
        <article class="chat-row chat-row-bot${animationClass}">
            <img src="/images/nicenito/clean/base.png" alt="" class="chat-mini-avatar">
            <div class="chat-bubble chat-bubble-bot">
                <p>${escapeHtml(content)}</p>
                ${formatSources(sources)}
            </div>
        </article>
    `;
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

    let history = [];
    let isSending = false;
    let idleTimer;
    let avatarTransitionTimer;
    let responseStateTimer;

    // Las imágenes de estado pesan varios cientos de KB. Si no se precargan, la
    // primera transición (p. ej. a "pensando") no alcanza a verse porque la
    // imagen aún se está descargando cuando el estado vuelve a cambiar.
    const preloadAvatarImages = () => {
        Object.values(nicenitoImages).forEach((src) => {
            const image = new Image();
            image.src = src;
        });
    };

    // Marca el contenedor como "conversando" para que el layout móvil pliegue la
    // ilustración de bienvenida y deje a Nicenito como avatar pequeño en la
    // cabecera. En escritorio la clase no tiene estilos asociados.
    const setChattingState = () => {
        root.classList.toggle('chatting', history.length > 0);
    };

    // Autocrecer del textarea hasta un máximo. Solo en móvil (≤560px) para no
    // alterar el comportamiento de la vista de escritorio.
    const autoGrowQuery = window.matchMedia('(max-width: 560px)');
    const TEXTAREA_MAX_PX = 132;
    const autoGrowTextarea = () => {
        if (!autoGrowQuery.matches) {
            textarea.style.height = '';
            return;
        }
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.min(textarea.scrollHeight, TEXTAREA_MAX_PX)}px`;
    };
    autoGrowQuery.addEventListener('change', autoGrowTextarea);

    // En teléfonos (≤560px) el hero de NicenoBot (imagen + estado) se reubica
    // dentro del panel de chat, justo bajo la cabecera, para lograr el layout
    // móvil de una sola columna. En escritorio vuelve a su escena lateral.
    const mobileLayoutQuery = window.matchMedia('(max-width: 560px)');
    const chatPanel = root.querySelector('.niceno-chat-panel');
    const chatPanelHeader = chatPanel ? chatPanel.querySelector('header') : null;
    const avatarHome = nicenitoAvatar.parentElement;
    const applyMobileLayout = () => {
        if (!chatPanel || !chatPanelHeader || !avatarHome) {
            return;
        }
        if (mobileLayoutQuery.matches) {
            if (nicenitoAvatar.parentElement !== chatPanel) {
                chatPanelHeader.insertAdjacentElement('afterend', nicenitoAvatar);
            }
        } else if (nicenitoAvatar.parentElement !== avatarHome) {
            avatarHome.appendChild(nicenitoAvatar);
        }
    };
    applyMobileLayout();
    mobileLayoutQuery.addEventListener('change', applyMobileLayout);

    const clearAvatarTimers = () => {
        window.clearTimeout(idleTimer);
        window.clearTimeout(avatarTransitionTimer);
        window.clearTimeout(responseStateTimer);
    };

    const scheduleReturnToBase = (delay = 4200) => {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(() => {
            if (!isSending && textarea.value.trim() === '') {
                setNicenoBotState('base');
            }
        }, delay);
    };

    const setNicenoBotState = (state) => {
        const nextState = nicenitoImages[state] ? state : 'base';

        nicenitoAvatar.dataset.state = nextState;
        // Single state hook for the avatar image, status text, and CSS halo effects.
        nicenitoAvatar.classList.remove(
            'nicenito--base',
            'nicenito--escuchando',
            'nicenito--pensando',
            'nicenito--respondiendo',
            'nicenito--explicando',
            'nicenito--celebrando',
            'nicenito--finalizando',
        );
        nicenitoAvatar.classList.add(`nicenito--${nextState}`);
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
        setChattingState();

        if (history.length === 0) {
            messagesContainer.innerHTML = `
                <article class="chat-row chat-row-bot">
                    <img src="/images/nicenito/clean/base.png" alt="" class="chat-mini-avatar">
                    <div class="chat-bubble chat-bubble-bot">
                        <p>Hola, soy NicenoBot. Puedes preguntarme sobre la fe, la oraci\u00f3n, Jes\u00fas, el Evangelio o los sacramentos.</p>
                    </div>
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
        setChattingState();
        messagesContainer.insertAdjacentHTML('beforeend', createMessageMarkup(message));
        scrollToBottom();
    };

    const sendMessage = async (message) => {
        const trimmedMessage = message.trim();

        if (!trimmedMessage) {
            setError('Escribe una pregunta para continuar.');
            setNicenoBotState('base');
            return;
        }

        if (trimmedMessage.length > 500) {
            setError('Tu mensaje no puede superar los 500 caracteres.');
            setNicenoBotState('base');
            return;
        }

        clearAvatarTimers();
        setError();

        // Historial breve (sin fuentes) que ya mantiene el frontend, anterior
        // al mensaje actual; el backend usa a lo sumo los últimos mensajes.
        const historyPayload = history
            .slice(-4)
            .map(({ role, content }) => ({ role, content }));

        appendMessage({ role: 'user', content: trimmedMessage });
        textarea.value = '';
        syncCounter();
        autoGrowTextarea();
        // Mientras genera la respuesta, NicenoBot está "pensando".
        setNicenoBotState('pensando');
        setLoading(true);

        try {
            const response = await window.axios.post(endpoint, {
                message: trimmedMessage,
                history: historyPayload,
            });
            const assistantMessage = {
                role: 'assistant',
                content: response.data.answer,
                sources: response.data.sources ?? [],
            };

            setNicenoBotState('respondiendo');
            appendMessage(assistantMessage);

            const backendState = response.data.nicenito_state;

            responseStateTimer = window.setTimeout(() => {
                setNicenoBotState(backendState && nicenitoImages[backendState]
                    ? backendState
                    : evaluateResponseState({
                        answer: assistantMessage.content,
                        sources: assistantMessage.sources,
                    }));
                scheduleReturnToBase();
            }, prefersReducedMotion.matches ? 0 : 900);
        } catch (error) {
            history.pop();
            renderHistory();

            // Sesi\u00f3n expirada: 401 (sesi\u00f3n de participante) o 419 (CSRF/sesi\u00f3n
            // vencida, que ocurre antes del 401). En ambos casos volvemos al
            // acceso para reingresar, en vez de dejar al usuario atascado.
            const status = error?.response?.status;
            if (status === 401 || status === 419) {
                const accessUrl = root.dataset.accessUrl;
                if (accessUrl) {
                    window.location.href = accessUrl;
                    return;
                }
            }

            const validationMessage = error?.response?.data?.errors?.message?.[0];
            const serverMessage = error?.response?.data?.message;
            setError(validationMessage ?? serverMessage ?? 'Ocurri\u00f3 un problema al responder. Intenta de nuevo en unos segundos.');
            setNicenoBotState('base');
        } finally {
            setLoading(false);
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
            setNicenoBotState('escuchando');
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
        autoGrowTextarea();

        if (isSending) {
            return;
        }

        if (textarea.value.trim() === '') {
            setNicenoBotState('base');
            return;
        }

        setNicenoBotState('escuchando');
    });

    suggestedButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            window.clearTimeout(responseStateTimer);
            window.clearTimeout(idleTimer);
            textarea.value = button.dataset.question ?? '';
            syncCounter();
            autoGrowTextarea();
            setNicenoBotState('escuchando');
            await sendMessage(textarea.value);
        });
    });

    preloadAvatarImages();
    renderHistory();
    syncCounter();
    setNicenoBotState('base');
};

initializeCatequesisChat();
