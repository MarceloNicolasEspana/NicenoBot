import { createApp } from 'vue';
import NicenitoChatPage from './pages/NicenitoChatPage.vue';

// Punto de montaje de la interfaz Vue del chatbot. Solo existe cuando la vista
// Blade se renderiza con el flag NICENITO_CHAT_UI=vue. Los datos iniciales
// (seguros) llegan serializados en el atributo data-bootstrap.
const mount = document.querySelector('#nicenito-app');

if (mount) {
    let bootstrap = {};
    try {
        bootstrap = JSON.parse(mount.dataset.bootstrap ?? '{}');
    } catch {
        bootstrap = {};
    }

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    createApp(NicenitoChatPage, { ...bootstrap, csrfToken }).mount(mount);
}
