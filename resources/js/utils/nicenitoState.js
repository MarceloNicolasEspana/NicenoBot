// Fuente única de verdad sobre los estados visuales de NicenoBot.
//
// El backend manda el estado final en `nicenito_state`. El frontend solo puede
// originar estados transitorios (`escuchando`, `pensando`). Cualquier valor
// desconocido cae a `base` de forma segura.

export const NICENITO_STATES = [
    'base',
    'escuchando',
    'pensando',
    'respondiendo',
    'explicando',
    'celebrando',
    'finalizando',
];

// Estados que el frontend puede usar por su cuenta (no provienen del backend).
export const TRANSIENT_STATES = ['escuchando', 'pensando'];

const ALT_TEXT = {
    base: 'NicenoBot está en reposo',
    escuchando: 'NicenoBot está escuchando',
    pensando: 'NicenoBot está pensando',
    respondiendo: 'NicenoBot está respondiendo',
    explicando: 'NicenoBot está explicando',
    celebrando: 'NicenoBot está celebrando',
    finalizando: 'NicenoBot está cerrando la conversación',
};

const STATUS_TEXT = {
    base: 'NicenoBot está listo para acompañarte.',
    escuchando: 'NicenoBot te escucha.',
    pensando: 'NicenoBot está pensando.',
    respondiendo: 'NicenoBot está respondiendo.',
    explicando: 'NicenoBot está explicando.',
    celebrando: 'NicenoBot te anima a seguir adelante.',
    finalizando: 'NicenoBot está cerrando este momento contigo.',
};

const DEFAULT_BASE_PATH = '/images/nicenito/clean/';

/** Devuelve un estado válido o `base` como fallback seguro. */
export function normalizeState(value) {
    return NICENITO_STATES.includes(value) ? value : 'base';
}

export function isTransientState(value) {
    return TRANSIENT_STATES.includes(value);
}

export function altText(state) {
    return ALT_TEXT[normalizeState(state)];
}

export function statusText(state) {
    return STATUS_TEXT[normalizeState(state)];
}

/** Construye la URL pública de la imagen de un estado. */
export function imageFor(state, basePath = DEFAULT_BASE_PATH) {
    return `${basePath}${normalizeState(state)}.png`;
}

export function allImagePaths(basePath = DEFAULT_BASE_PATH) {
    return NICENITO_STATES.map((state) => imageFor(state, basePath));
}
