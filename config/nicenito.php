<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Zona horaria de evaluación
    |--------------------------------------------------------------------------
    |
    | Se usa para decidir si un contenido semanal está vigente (starts_at /
    | ends_at). El contenido se administra pensando en Chile, así que por
    | defecto evaluamos en America/Santiago aunque la app corra en UTC.
    |
    */

    'timezone' => env('NICENITO_TIMEZONE', 'America/Santiago'),

    /*
    |--------------------------------------------------------------------------
    | Rol con acceso administrativo
    |--------------------------------------------------------------------------
    |
    | La autorización del panel usa spatie/laravel-permission. Solo usuarios
    | autenticados con este rol pueden entrar a /admin/nicenito. El rol se crea
    | en el seeder (db:seed) y se asigna a los usuarios administradores.
    |
    */

    'admin_role' => env('NICENITO_ADMIN_ROLE', 'nicenito-admin'),

    /*
    |--------------------------------------------------------------------------
    | Interfaz del chatbot (feature flag)
    |--------------------------------------------------------------------------
    |
    | Controla qué interfaz se renderiza en /chatbot-catequesis:
    |   - 'vue'    : nueva interfaz basada en componentes Vue (por defecto).
    |   - 'legacy' : interfaz Blade + JavaScript anterior (fallback temporal).
    |
    | Ambas consumen el mismo endpoint y contrato. Permite volver atrás sin
    | desplegar código si la interfaz nueva presenta algún problema.
    |
    */

    'chat_ui' => env('NICENITO_CHAT_UI', 'vue') === 'legacy' ? 'legacy' : 'vue',

    /*
    |--------------------------------------------------------------------------
    | Preguntas sugeridas del chatbot
    |--------------------------------------------------------------------------
    |
    | Se muestran como atajos al iniciar la conversación. NO llaman a Gemini:
    | solo rellenan el campo de texto. Mantenerlas breves y catequéticas.
    |
    */

    'suggested_questions' => [
        'Explícame el Evangelio del domingo',
        '¿Cómo puedo rezar mejor?',
        '¿Qué es la confesión?',
        '¿Qué significa tener fe?',
        'Dame una pregunta para reflexionar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging detallado (solo desarrollo)
    |--------------------------------------------------------------------------
    |
    | Cuando es true se registran datos extra (intención detectada, títulos de
    | contenido recuperado, etc.). Nunca registrar el texto completo de las
    | preguntas de los jóvenes en producción.
    |
    */

    'detailed_logging' => (bool) env('NICENITO_DETAILED_LOGGING', false),

    /*
    |--------------------------------------------------------------------------
    | Acceso de participantes (jóvenes)
    |--------------------------------------------------------------------------
    |
    | Límites de uso y de intentos de acceso. Los participantes no usan correo
    | ni contraseña: ingresan con un código personal + PIN de 6 dígitos.
    |
    */

    'participant' => [
        // Preguntas permitidas por ventana larga.
        'questions_per_window' => 5,
        'questions_window_minutes' => 15,
        // Tiempo mínimo entre preguntas (anti-spam).
        'question_cooldown_seconds' => 8,
        // Intentos de acceso (código + PIN) por ventana.
        'login_max_attempts' => 5,
        'login_window_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retención de preguntas
    |--------------------------------------------------------------------------
    |
    | Días que se conservan los textos de pregunta/respuesta antes de que el
    | comando nicenito:prune-questions los anonimice. Se conservan métricas
    | agregadas no identificables.
    |
    */

    'question_retention_days' => (int) env('NICENITO_QUESTION_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Límites de contexto enviado a Gemini
    |--------------------------------------------------------------------------
    */

    'context' => [
        'weekly_max_chars' => 1800,
        'fixed_max_chars' => 1200,
        'max_fixed_contents' => 2,
        'total_max_chars' => 5000,
        'max_key_ideas' => 5,
        'history_messages' => 2,
        // Puntaje mínimo para considerar relevante un contenido fijo.
        'min_relevance_score' => 2.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorías de contenido fijo
    |--------------------------------------------------------------------------
    |
    | Lista centralizada (no hay tabla de categorías en esta fase).
    |
    */

    'categories' => [
        'Jesús y Trinidad',
        'Biblia',
        'Oración',
        'Confesión',
        'Pecado',
        'Sacramentos',
        'Misa',
        'Confirmación',
        'Virgen María',
        'Iglesia',
        'Vocación',
        'Fe y esperanza',
    ],

    /*
    |--------------------------------------------------------------------------
    | Palabras demasiado genéricas
    |--------------------------------------------------------------------------
    |
    | No deben, por sí solas, hacer relevante un contenido. Sirven para evitar
    | que "Jesús", "Dios" o "fe" arrastren contenido irrelevante.
    |
    */

    'stopwords' => [
        'jesus', 'dios', 'fe', 'iglesia', 'senor', 'cristo', 'espiritu',
        'que', 'como', 'por', 'para', 'una', 'los', 'las', 'del', 'con',
        'sobre', 'mas', 'pero', 'este', 'esta', 'son', 'hay', 'cual',
    ],

];
