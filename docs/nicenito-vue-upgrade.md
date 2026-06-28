# Modernización de la interfaz de NicenoBot (Vue)

Migración incremental de la interfaz del chatbot de **Blade + JavaScript** a
**Vue 3**, manteniendo intacto todo el backend (autenticación de participantes,
sesiones, middleware, CSRF, rate limits, registro de preguntas, contenidos,
Gemini, fuentes y logs).

---

## Arquitectura antes y después

### Antes

```
GET /chatbot-catequesis
  └─ catequesis/chatbot.blade.php  (HTML completo)
       └─ resources/js/catequesis-chat.js  (DOM imperativo + window.axios)
            └─ POST /chatbot-catequesis/preguntar
```

### Después (flag = vue)

```
GET /chatbot-catequesis
  └─ catequesis/chatbot.blade.php
       ├─ @if vue  → <div id="nicenito-app" data-bootstrap="…">
       │              └─ resources/js/nicenito-chat.js  (monta Vue)
       │                   └─ pages/NicenitoChatPage.vue
       │                        └─ composables/useNicenitoChat.js → fetch()
       │                             └─ POST /chatbot-catequesis/preguntar
       └─ @else    → shell Blade + catequesis-chat.js  (legacy, sin cambios)
```

**El endpoint, su contrato, las rutas, el controlador `chat()`, el servicio,
los middleware y el rate limiting NO se modificaron.** Solo se añadió bootstrap
seguro a `CatequesisChatController::show()`.

---

## Componentes creados

```
resources/js/
├── nicenito-chat.js                    # Entry: monta Vue en #nicenito-app
├── pages/
│   └── NicenitoChatPage.vue            # Contenedor; coordina avatar/mensajes/composer
├── components/nicenito/
│   ├── NicenitoAvatar.vue              # Avatar con estados + preload + fallback a base.png
│   ├── ChatHeader.vue                  # Avatar compacto, marca, estado, botón salir
│   ├── ChatMessage.vue                 # Burbuja usuario/bot (texto plano seguro)
│   ├── ChatMessageList.vue             # Lista + autoscroll + indicador de carga
│   ├── ChatEmptyState.vue              # Saludo inicial con display_name
│   ├── SuggestedQuestions.vue          # Chips de preguntas sugeridas
│   ├── ChatComposer.vue                # Textarea 500, contador, Enter/Shift+Enter
│   ├── ChatSources.vue                 # Fuentes entregadas por Laravel (formato compacto)
│   ├── ReflectionCard.vue              # Pregunta de reflexión opcional
│   └── ChatRateLimitNotice.vue         # Aviso 429
├── composables/
│   └── useNicenitoChat.js              # Red, CSRF, estados de carga, errores
└── utils/
    └── nicenitoState.js                # Estados válidos, alt/label, rutas de imagen, fallback
```

Archivos backend tocados:
- `config/nicenito.php` — flag `chat_ui` + `suggested_questions`.
- `app/Http/Controllers/CatequesisChatController.php` — bootstrap seguro en `show()`.
- `resources/views/catequesis/chatbot.blade.php` — rama vue/legacy.
- `vite.config.js` / `package.json` — Vue + plugin + nuevo entry.

---

## Activar / desactivar la interfaz (`NICENITO_CHAT_UI`)

En `.env`:

```dotenv
# Interfaz nueva (Vue) — valor por defecto si la variable no existe
NICENITO_CHAT_UI=vue

# Volver temporalmente a la interfaz Blade/JS anterior
NICENITO_CHAT_UI=legacy
```

Tras cambiarla, limpiar caché de config si está cacheada:

```bash
php artisan config:clear
```

No requiere recompilar assets ni desplegar código: es un cambio de entorno.

---

## Contrato esperado del endpoint

`POST /chatbot-catequesis/preguntar` (sin cambios). El front envía solo:

```json
{ "message": "texto (≤500)", "history": [{ "role": "user|assistant", "content": "…" }] }
```

Nunca envía `participant_id`, API keys ni prompts. Respuesta esperada:

```json
{
  "answer": "texto",
  "sources": [{ "type": "Evangelio", "reference": "Mateo 10, 26-33" }],
  "reflection": "texto opcional o null",
  "nicenito_state": "base|escuchando|pensando|respondiendo|explicando|celebrando|finalizando",
  "needs_human_guidance": false
}
```

El frontend valida `nicenito_state` y hace fallback a `base` si llega un valor
desconocido. Las fuentes se muestran tal cual: nunca se construyen en el front.

---

## Estados de Nicenito e imágenes

- Imágenes en `public/images/nicenito/clean/<estado>.png` (juego usado por la UI).
- La ruta base es configurable vía bootstrap (`avatarBasePath`).
- El backend es la fuente de verdad del estado final (`nicenito_state`). El
  frontend solo origina los transitorios `escuchando` y `pensando`.

### Agregar un estado o imagen nuevos

1. Añadir el PNG en `public/images/nicenito/clean/<nuevo>.png`.
2. Registrar el estado en `resources/js/utils/nicenitoState.js`
   (`NICENITO_STATES`, `ALT_TEXT`, `STATUS_TEXT`).
3. Si el backend debe devolverlo, agregarlo en `CatequesisChatService`.
4. `npm run build`.

---

## Cómo probar localmente

```bash
npm install
npm run build      # o: npm run dev (HMR)
php artisan test --filter "NicenoBotChatUiTest|CatequesisChatTest"
```

Manual: ingresar como participante (`/nicenito/acceso`), abrir
`/chatbot-catequesis` y verificar saludo, pregunta catequética, seguimiento,
respuesta con fuentes/reflexión, estado `celebrando`, error 429, timeout,
móvil/escritorio y cierre de sesión.

### Pruebas automatizadas relevantes
- `NicenoBotChatUiTest` — flag vue/legacy y seguridad del bootstrap.
- `CatequesisChatTest` — protección de ruta, no-envío de `participant_id`,
  rate limit, fuentes desde Laravel, validación de 500 caracteres.

---

## Legacy: qué sigue siendo fallback y cómo retirarlo

Mientras la interfaz Vue no esté validada en producción, se conservan como
fallback (activables con `NICENITO_CHAT_UI=legacy`):

- `resources/js/catequesis-chat.js`
- La rama `@else` de `resources/views/catequesis/chatbot.blade.php`
- Reglas CSS legacy en `resources/css/app.css`
  (`.niceno-shell`, `.niceno-stage`, `.nicenito-*`, `.chat-*`, etc.)

### Pasos seguros para retirar el legacy (cuando Vue esté validado)

1. Confirmar en producción que `NICENITO_CHAT_UI=vue` funciona varios días.
2. Eliminar la rama `@else` del Blade y dejar solo el montaje Vue.
3. Quitar `resources/js/catequesis-chat.js` y su import en `resources/js/app.js`
   (revisar si `app.js` se usa en otras vistas antes de removerlo).
4. Eliminar las reglas CSS legacy del chat que ya no se referencien.
5. Retirar la rama `@if/@else` del flag y, opcionalmente, el flag en config.
6. `npm run build` y volver a correr la suite.
