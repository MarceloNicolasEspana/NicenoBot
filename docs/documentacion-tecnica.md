# Documentación técnica — NicenoBot

Documento de estudio del software. Describe la arquitectura, el modelo de datos,
los flujos, la seguridad y la operación del sistema **NicenoBot**, un asistente de
catequesis católica con un chatbot público (alimentado por Google Gemini sobre
contenido propio), un panel administrativo y acceso de participantes sin correo.

> Audiencia: desarrolladores que necesitan entender o mantener el proyecto.
> Todo lo aquí descrito corresponde al código real del repositorio.

---

## 1. Visión general

NicenoBot resuelve tres necesidades:

1. **Conversar**: los jóvenes preguntan sobre fe (Evangelio, oración, sacramentos…)
   y reciben respuestas redactadas por Gemini **usando solo contenido autorizado**
   por el equipo de catequesis (enfoque "RAG ligero", sin embeddings todavía).
2. **Administrar contenido**: un panel privado para crear contenido **semanal** y
   **fijo** que sirve de contexto al modelo.
3. **Acompañar**: cada pregunta queda asociada a un **participante** identificable
   por el equipo, con seguimiento y resguardo de privacidad.

El desarrollo se hizo en fases:
- **Fase 1**: backend de contenidos + integración con Gemini + panel admin.
- **Fase 2 (acceso)**: participantes con código/PIN, registro de preguntas, panel.
- **Capa visual**: landing, manuales y rediseño de fondo.

---

## 2. Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Lenguaje / framework | PHP 8.2, **Laravel 12** |
| Base de datos | MySQL/MariaDB (producción/local), **SQLite en memoria** (tests) |
| Frontend | **Blade** + **Tailwind CSS 4** + **Vite 7** + **axios** (sin Vue/Inertia/Livewire) |
| Autorización | **spatie/laravel-permission** (roles) |
| IA | **Google Gemini** (`gemini-2.5-flash`) vía API HTTP |
| Testing | **PHPUnit 11** |
| Estilo de código | **Laravel Pint** |

No hay SPA: las vistas son Blade renderizadas en servidor; el chat usa `axios`
contra un endpoint propio.

---

## 3. Arquitectura general

### 3.1 Capas

```
Navegador (Blade + Tailwind + axios)
        │  POST /chatbot-catequesis/preguntar
        ▼
CatequesisChatController  ──►  CatequesisChatService  (orquestador)
                                   ├─► NicenoBotContentContextService  (recupera contexto + fuentes)
                                   │        └─► NicenoBotContent (Eloquent)
                                   └─► GeminiModelService  (llama a la API de Gemini)
                                   └─► NicenoBotQuestion (persiste la pregunta)
```

### 3.2 Flujo de una pregunta (resumen)

1. Middleware `participant.auth` + `participant.onboarded` validan la sesión.
2. El controlador aplica **rate limiting** por participante.
3. `CatequesisChatService::respond()`:
   - Si es **saludo/despedida** → respuesta local breve (no llama a Gemini).
   - Si no hay **contenido autorizado** suficiente → respuesta prudente (no llama a Gemini).
   - En otro caso → recupera contexto y pide a Gemini que **redacte**.
4. Las **fuentes** las arma el backend (no el modelo).
5. Se guarda una fila en `nicenito_questions` y se devuelve el contrato JSON.

---

## 4. Estructura de directorios relevante

```
app/
  Console/Commands/PruneNicenoBotQuestions.php      # retención/anonimización
  Enums/
    NicenoBotContentType.php   (weekly|fixed)
    NicenoBotContentStatus.php (draft|published|archived)
    FollowUpStatus.php        (none|review|catechist_follow_up|resolved)
  Http/
    Controllers/
      CatequesisChatController.php                  # página + endpoint del chat
      Auth/LoginController.php                      # login admin (sesión)
      Participant/AccessController.php              # acceso joven (código+PIN)
      Participant/OnboardingController.php          # cambio de PIN + privacidad
      Admin/NicenoBotDashboardController.php
      Admin/NicenoBotContentController.php
      Admin/ParticipantController.php
      Admin/NicenoBotQuestionController.php
    Middleware/
      NicenoBotAdmin.php                            # rol Spatie 'nicenito-admin'
      EnsureParticipantAuthenticated.php           # sesión de participante
      EnsureParticipantOnboarded.php               # PIN cambiado + aviso aceptado
    Requests/NicenoBotContentRequest.php            # validación de contenidos
  Models/
    User.php (HasRoles), NicenoBotContent.php,
    Participant.php, NicenoBotQuestion.php
  Services/
    CatequesisChatService.php                      # orquestador del chat
    NicenoBotContentContextService.php              # recuperación de contexto
    GeminiModelService.php                         # cliente de Gemini
config/
  nicenito.php          # categorías, límites, retención, rol admin, etc.
  services.php          # api_key + modelo de Gemini
database/
  migrations/…          # nicenito_contents, participants, nicenito_questions, permission_tables
  factories/…           # NicenoBotContent, Participant, NicenoBotQuestion
  seeders/…             # DatabaseSeeder, NicenoBotContentSeeder, ParticipantSeeder
resources/
  views/                # welcome, catequesis/chatbot, admin/*, participant/*, auth/login
  js/catequesis-chat.js # lógica del chat (estados, historial, errores)
  css/app.css           # tokens de tema, fondo, componentes NicenoBot
routes/web.php          # casi todo (chat usa sesión, por eso va en web)
```

---

## 5. Modelo de datos

### 5.1 `nicenito_contents`
Contenido catequético. Una sola tabla para semanal y fijo.

Campos clave: `type` (enum weekly|fixed), `status` (draft|published|archived),
`category`, `title`, `slug` (único), `gospel_reference`, `summary`, `content`,
y JSON: `biblical_references`, `catechism_references`, `key_ideas`, `faq`
(`[{question, answer}]`), `reflection_questions`, `tags`. Para semanal:
`starts_at`/`ends_at`. `created_by` → users. `softDeletes`.

Modelo `App\Models\NicenoBotContent`:
- Casts de enums y arrays; `starts_at`/`ends_at` como datetime.
- Scopes: `published()`, `weekly()`, `fixed()`, `activeWeekly()`.
- `isActiveWeekly()`: publicado **y** fecha actual dentro del rango, evaluado en
  `config('nicenito.timezone')` (por defecto `America/Santiago`).
- `hasPublishedWeeklyOverlap()`: regla de no solapamiento de semanales publicados.

### 5.2 `participants`
Jóvenes. **No** usan correo ni contraseña.

Campos: `full_name` (solo equipo), `display_name` (visible en chat),
`group_name`, `access_code` (único, `NCE-XXXX`), `pin_hash`, `is_active`,
`must_change_pin`, `last_login_at`, `privacy_notice_accepted_at`, `softDeletes`.

Modelo `App\Models\Participant`:
- `pin_hash` en `$hidden`; `setPin()`/`checkPin()` con `Hash`.
- `generateAccessCode()`: `NCE-` + 4 caracteres de un alfabeto sin ambiguos (sin 0/O/1/I).
- `generatePin()`: 6 dígitos.
- `safeName()`: nunca expone el nombre completo en el chat.
- Relación `questions()`.

### 5.3 `nicenito_questions`
Registro de cada turno del joven.

Campos: `participant_id`, `weekly_content_id` (nullable),
`question`, `answer`, `sources` (JSON), `detected_category`,
`used_gemini`, `has_weekly_content`, `fixed_contents_count`,
`needs_human_guidance`, `follow_up_status` (enum), `follow_up_notes`,
`follow_up_by` → users, `answered_at`.

Modelo `App\Models\NicenoBotQuestion`: casts de bool/array/enum/datetime;
relaciones `participant()`, `weeklyContent()`, `followUpBy()`.

### 5.4 Relaciones (resumen)
```
User 1───* NicenoBotContent      (created_by)
User 1───* NicenoBotQuestion     (follow_up_by)
User *───* Role/Permission      (Spatie)
Participant 1───* NicenoBotQuestion
NicenoBotContent 1───* NicenoBotQuestion  (weekly_content_id, nullOnDelete)
```

---

## 6. Configuración

### `config/services.php → nicenobot`
```php
'nicenobot' => [
    'api_key' => env('NICENOBOT_API_KEY'),
    'model'   => env('GEMINI_MODEL', 'gemini-2.5-flash'),
],
```

### `config/nicenito.php`
- `timezone` (`America/Santiago` por defecto): vigencia del contenido semanal.
- `admin_role` (`nicenito-admin`): rol que da acceso al panel.
- `detailed_logging` (`NICENITO_DETAILED_LOGGING`): logs ampliados solo en local.
- `participant`: límites de uso y de acceso (ver §10).
- `question_retention_days` (`NICENITO_QUESTION_RETENTION_DAYS`, 90): retención.
- `context`: límites de tamaño del contexto a Gemini (ver §8).
- `categories`: lista central de categorías de contenido fijo.
- `stopwords`: palabras genéricas que no deben, por sí solas, recuperar contenido.

### Variables de entorno relevantes (`.env`)
```
NICENOBOT_API_KEY=...           # clave de Google AI (Gemini)
GEMINI_MODEL=gemini-2.5-flash
NICENITO_ADMIN_ROLE=nicenito-admin
NICENITO_DETAILED_LOGGING=false
NICENITO_QUESTION_RETENTION_DAYS=90
NICENITO_TIMEZONE=America/Santiago
DB_ENGINE=InnoDB                # fuerza InnoDB (claves foráneas + índices utf8mb4)
```

> Nota de despliegue: en `config/database.php` el `engine` de mysql/mariadb se fija
> a `InnoDB` y en `AppServiceProvider::boot()` se hace `Schema::defaultStringLength(191)`
> para evitar el error 1071 en servidores con límite de clave bajo (MyISAM/Aria).

---

## 7. Autenticación y autorización

Hay **dos** sistemas independientes, deliberadamente separados.

### 7.1 Administradores / catequistas
- Login propio mínimo con el **guard de sesión** de Laravel (`Auth::attempt`),
  sin paquetes de scaffolding. Rutas `/login`, `/logout`.
- Autorización por **rol Spatie** `nicenito-admin`, aplicada con el middleware
  `nicenito.admin` (alias en `bootstrap/app.php`).
  - Sin sesión → redirige a `/login`.
  - Con sesión pero sin rol → `403`.
- Asignación de rol (manual): `User->assignRole('nicenito-admin')`.

### 7.2 Participantes (jóvenes)
- **Sin correo ni contraseña**: ingresan con `access_code` + `pin`.
- El identificador `participant_id` vive **solo en la sesión del servidor**; nunca
  se lee del request/query/localStorage (defensa probada en tests).
- Middlewares:
  - `participant.auth` (`EnsureParticipantAuthenticated`): valida que exista
    `participant_id` en sesión y que el participante esté activo; expone el
    participante en `request->attributes('participant')`. Si no hay sesión:
    redirige a `/nicenito/acceso` (o `401` si la petición espera JSON).
  - `participant.onboarded` (`EnsureParticipantOnboarded`): exige PIN ya cambiado
    (`must_change_pin = false`) y aviso aceptado; si falta algo, redirige al paso
    correspondiente. Se aplica **solo** al chatbot y su endpoint, para evitar
    bucles en las pantallas de onboarding.
- **Onboarding** (primer ingreso): cambiar PIN temporal → aceptar aviso de
  privacidad (`privacy_notice_accepted_at`). La sesión se **regenera** al entrar
  y al salir.

---

## 8. Flujo del chatbot (RAG ligero)

### 8.1 `NicenoBotContentContextService::build($pregunta)`
Recupera el contexto autorizado y devuelve una estructura:
```php
[
  'weekly_content' => NicenoBotContent|null,   // semanal vigente
  'fixed_contents' => Collection,             // hasta 2 fijos relevantes
  'context_text'   => string,                 // contexto compacto para el prompt
  'sources'        => array,                  // fuentes verificadas por backend
  'confidence'     => float,                  // 0..1
]
```
Cómo decide:
- **Semanal**: el `activeWeekly()` (si existe) siempre entra al contexto.
- **Fijos**: puntaje léxico por campos con pesos (title/tags/faq=3, category=2.5,
  key_ideas/gospel=2, summary=1) + bonus por coincidencia de **frases** (bigramas).
  Se filtran `stopwords` (Jesús, Dios, fe…) para que una palabra genérica no
  arrastre contenido irrelevante. Se toman los 2 de mayor puntaje sobre el umbral
  `min_relevance_score`.
- **Límites** (`config('nicenito.context')`): semanal ≤ 1.800 chars, cada fijo
  ≤ 1.200, máximo 2 fijos, contexto total ≤ 5.000 chars, ≤ 5 ideas clave.
- **Fuentes**: se generan a partir de lo realmente almacenado
  (`Evangelio`/`Biblia`/`Catecismo` con su referencia y título). El modelo **no**
  inventa fuentes.

> Diseño pensado para Fase 2: reemplazar el scorer léxico por embeddings/búsqueda
> semántica sin tocar el resto del flujo (el contrato del servicio no cambia).

### 8.2 `GeminiModelService`
- `generate($pregunta, $contexto, $historial)`: arma `systemInstruction` (prompt
  doctrinal con reglas: usar solo el contexto, no inventar citas, 90–180 palabras,
  no pedir datos personales, etc.), agrega el historial breve y la pregunta, y
  llama a `…:generateContent`. Devuelve `ok`, `answer`, `usage` (tokens),
  `finish_reason`, `status`.
- `buildUserPrompt()`: expuesto para la **vista previa** del panel (muestra el
  contexto exacto que se enviaría, sin revelar la API key).
- Se registra como **singleton** en `AppServiceProvider` vía `fromConfig()`.

### 8.3 `CatequesisChatService::respond($mensaje, $historial)`
Orquesta y devuelve el contrato + una clave `meta` (que el controlador retira
antes de responder):
```json
{
  "answer": "…",
  "sources": [{ "type": "Evangelio", "reference": "Mateo 10, 26-33", "title": "…" }],
  "reflection": null,
  "nicenito_state": "explicando",
  "needs_human_guidance": false
}
```
`meta` incluye: `used_gemini`, `has_weekly_content`, `weekly_content_id`,
`fixed_contents_count`, `detected_category`, `intent` — usados para persistir la
pregunta. Estados (`nicenito_state`): `respondiendo`, `explicando`, `finalizando`, etc.

---

## 9. Registro de preguntas, privacidad y logging

- El controlador del chat **resuelve el participante desde la sesión**, llama al
  servicio, **persiste** `NicenoBotQuestion` y retira `meta` antes de responder
  (el contrato del frontend se mantiene idéntico).
- **Logging respetuoso**: se registran solo métricas (`participant_id`, longitud
  del mensaje, categoría, uso de Gemini, latencia, `needs_human_guidance`, errores).
  **Nunca** se registran: API keys, prompts/contexto completos, texto íntegro de
  preguntas/respuestas, PIN ni código de acceso.
- **Retención**: el texto sensible vive en `nicenito_questions` y se **anonimiza**
  pasado `question_retention_days` con el comando del §14, conservando solo
  estadísticas agregadas.

---

## 10. Rate limiting

Vía `Illuminate\Support\Facades\RateLimiter` (cache):

| Acción | Límite | Clave |
|--------|--------|-------|
| Preguntas (enfriamiento) | 1 cada **8 s** | `nicenito-q-cooldown:{id}` |
| Preguntas (ventana) | **5 cada 15 min** | `nicenito-q-window:{id}` |
| Intentos de acceso | **5 cada 15 min** | `participant-login:{ip}` |

Al exceder preguntas se devuelve `429` con un `message` amable (el frontend lo
muestra). Los intentos de login fallidos dan un mensaje **genérico** (no revelan
si el código existe).

---

## 11. Panel administrativo

Prefijo `/admin/nicenito`, todo bajo `nicenito.admin`.

- **Dashboard**: semanal activo, próximo semanal, fijos publicados, borradores.
- **Contenidos** (`NicenoBotContentController`): CRUD + `publish`/`archive`/
  `duplicate`/`preview`. Validación en `NicenoBotContentRequest`:
  - Campos múltiples se ingresan como texto (uno por línea; FAQ como
    `pregunta :: respuesta`) y se convierten a arrays en `prepareForValidation()`.
  - Botones "Guardar borrador"/"Publicar" mapean a `status` vía `action`.
  - Regla de **no solapamiento** de semanales publicados.
  - **Vista previa**: corre el pipeline real (contexto + Gemini) sin guardar nada.
- **Participantes** (`ParticipantController`): crear (genera código + PIN temporal,
  `must_change_pin=true`), **vista de credenciales imprimible** (el PIN se muestra
  una sola vez vía flash de sesión), editar, activar/desactivar, regenerar PIN,
  regenerar código, eliminar (soft), enlace a su historial.
- **Preguntas** (`NicenoBotQuestionController`): índice con filtros (participante,
  grupo, fecha, categoría, semanal, estado, "requiere acompañamiento", "sin
  contenido", "sin respuesta"), detalle y **seguimiento** (`follow_up_status` +
  notas privadas + `follow_up_by`).

---

## 12. Rutas principales

| Método | URI | Nombre | Protección |
|--------|-----|--------|------------|
| GET | `/` | — | pública (landing) |
| GET/POST | `/nicenito/acceso` | `participant.access.*` | pública |
| GET/POST | `/nicenito/cambiar-pin` | `participant.pin.*` | `participant.auth` |
| GET/POST | `/nicenito/aviso` | `participant.privacy.*` | `participant.auth` |
| POST | `/nicenito/salir` | `participant.logout` | `participant.auth` |
| GET | `/chatbot-catequesis` | `chatbot.show` | `participant.auth`+`onboarded` |
| POST | `/chatbot-catequesis/preguntar` | `chatbot.chat` | `participant.auth`+`onboarded` |
| GET/POST | `/login`,`/logout` | `login`,`logout` | pública / sesión |
| * | `/admin/nicenito/*` | `admin.nicenito.*` | `nicenito.admin` |

> El endpoint del chat vive en `routes/web.php` (no en `api.php`) **a propósito**:
> necesita el grupo `web` para tener **sesión** del participante y CSRF. `routes/api.php`
> quedó vacío.

---

## 13. Frontend

- **Blade + Tailwind 4** vía `@vite`. Sin framework JS.
- `resources/js/catequesis-chat.js`: maneja el envío con `axios`, el **historial
  breve** en memoria, los **estados visuales** de NicenoBot (imágenes en
  `public/images/nicenito/clean/*`), el contador de 500 caracteres, y los errores
  (incluye redirección a `/nicenito/acceso` ante `401`, y mensajes de `429`).
- CSRF: el chat va por `web`; axios envía automáticamente la cookie `XSRF-TOKEN`.
- **Tema** (`resources/css/app.css`): tokens `--niceno-*` (crema, oro, burdeos,
  tinta). Fondo **plano cálido** (degradado crema) + **motivo de arcos** SVG muy
  tenue (`opacity 0.10`, burdeos) con `mask-image` que lo desvanece hacia el centro.
  Componentes propios: `niceno-shell`, `nicenito-arch`/`nicenito-halo` (marco del
  santo), `niceno-chip`, burbujas de chat, animaciones del halo por estado.
- Tras cambios de Blade/CSS hay que **recompilar**: `npm run build` (o `npm run dev`).

---

## 14. Comando de retención

`app/Console/Commands/PruneNicenoBotQuestions.php` — firma `nicenito:prune-questions`
(`--days=` opcional). Anonimiza filas más antiguas que la retención: pone a `null`
`question`, `answer`, `sources`, `follow_up_notes`, conservando métricas
(categoría, uso de IA, conteos, fechas, seguimiento). Solo registra el **conteo**.

No se activa solo. Para programarlo, en `routes/console.php`:
```php
use Illuminate\Support\Facades\Schedule;
Schedule::command('nicenito:prune-questions')->daily();
```
y correr el scheduler del sistema (`php artisan schedule:work` o cron).

---

## 15. Pruebas

PHPUnit con SQLite en memoria (`RefreshDatabase`) y `Http::fake()` para **no
consumir Gemini**. Cobertura principal:
- `CatequesisChatTest`: página protegida, validación 500, saludo sin Gemini,
  persistencia de la pregunta del participante de sesión, el frontend **no** puede
  fijar `participant_id`, rate limit.
- `ParticipantAccessTest`: login correcto/incorrecto, inactivo, cambio de PIN,
  bloqueo de chatbot por `must_change_pin`, límite de intentos.
- `NicenoBotParticipantAdminTest`: ver participantes/preguntas, crear participante
  con credenciales, no autorizado, marcar seguimiento.
- `NicenoBotContentContextTest`: semanal vigente/vencido, recuperación correcta de
  fijos por Trinidad, no arrastrar por palabras genéricas.
- `NicenoBotAdminTest`: auth/rol, crear fijo, solapamiento de semanales.
- `PruneNicenoBotQuestionsTest`: anonimización conservando métricas.

Ejecutar: `php artisan test`. Estilo: `./vendor/bin/pint`.

---

## 16. Datos de prueba (seeders)

`php artisan db:seed` ejecuta:
- **Admin demo**: `admin@nicenito.test` / `password` (con rol `nicenito-admin`).
- **Contenidos**: 2 fijos (Jesús verdadero Dios y verdadero hombre; La Santísima
  Trinidad) + 1 semanal vigente ("No tengan miedo", Mateo 10, 26-33).
- **Participante demo**: `NCE-DEMO` / PIN `123456`, grupo "Confirmación 2026",
  con 2 preguntas de ejemplo. **No usar en producción.**

---

## 17. Puesta en marcha

```bash
composer install
cp .env.example .env && php artisan key:generate
# Configurar en .env: DB_*, NICENOBOT_API_KEY, NICENITO_ADMIN_ROLE, DB_ENGINE=InnoDB
php artisan migrate          # crea tablas (incluye permisos de Spatie)
php artisan db:seed          # admin + contenidos + participante demo
npm install && npm run build # assets (Tailwind/Vite)
php artisan serve            # o el servidor que uses
```

Para desarrollo en caliente: `composer run dev` (levanta serve + queue + pail + vite).

---

## 18. Decisiones de diseño (por qué)

- **Una sola tabla `nicenito_contents`** para semanal y fijo: simplicidad en Fase 1.
- **Fuentes generadas por el backend**: evita que el modelo invente citas.
- **Dos sistemas de auth separados**: el administrador (rol Spatie) y el joven
  (sesión + código/PIN) tienen necesidades y superficies de ataque distintas.
- **`participant_id` solo en sesión**: impide suplantación desde el cliente.
- **Endpoint del chat en `web`**: para tener sesión/CSRF del participante.
- **RAG léxico (no embeddings) en Fase 1**: barato y suficiente; el servicio de
  contexto encapsula el algoritmo para sustituirlo después.
- **Privacidad por defecto**: logs sin contenido sensible + retención con
  anonimización.

---

## 19. Fuera de alcance / Fase 2

QR individual por participante, grupos de catequesis como entidad, permisos por
catequista (ver solo su grupo), exportación CSV, dashboard estadístico anonimizado,
y para el contenido: carga de PDF/DOCX, extracción de texto, **embeddings** y
búsqueda semántica (vector DB), historial completo de conversaciones.

---

## 20. Glosario

- **Contenido semanal**: tema vigente por fechas; uno activo a la vez.
- **Contenido fijo**: doctrina reutilizable por categoría.
- **Contexto**: extracto compacto de contenido autorizado que se envía a Gemini.
- **Participante**: joven que accede con código + PIN.
- **Onboarding**: cambio de PIN temporal + aceptación del aviso de privacidad.
- **Seguimiento (follow-up)**: estado + notas que el equipo asigna a una pregunta.
- **Anonimización**: borrado del texto sensible conservando métricas agregadas.

---

### Documentos relacionados
- [Manual del estudiante](manual-estudiante.md)
- [Manual del catequista](manual-catequista.md)
