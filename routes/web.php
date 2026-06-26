<?php

use App\Http\Controllers\Admin\NicenoBotContentController;
use App\Http\Controllers\Admin\NicenoBotDashboardController;
use App\Http\Controllers\Admin\NicenoBotQuestionController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatequesisChatController;
use App\Http\Controllers\Participant\AccessController;
use App\Http\Controllers\Participant\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // La portada no debe depender de la BD; si algo falla, omitimos el anuncio.
    $weekly = null;
    try {
        $weekly = \App\Models\NicenitoContent::query()->activeWeekly()->first();
    } catch (\Throwable $e) {
        // Sin anuncio del Evangelio de la semana.
    }

    return view('welcome', ['weekly' => $weekly]);
});

// --- Acceso de participantes (jóvenes) ----------------------------------------
Route::get('/nicenito/acceso', [AccessController::class, 'show'])->name('participant.access.show');
Route::post('/nicenito/acceso', [AccessController::class, 'login'])->name('participant.access.login');

Route::middleware('participant.auth')->group(function () {
    Route::post('/nicenito/salir', [AccessController::class, 'logout'])->name('participant.logout');

    Route::get('/nicenito/cambiar-pin', [OnboardingController::class, 'showPin'])->name('participant.pin.show');
    Route::post('/nicenito/cambiar-pin', [OnboardingController::class, 'updatePin'])->name('participant.pin.update');

    Route::get('/nicenito/aviso', [OnboardingController::class, 'showPrivacy'])->name('participant.privacy.show');
    Route::post('/nicenito/aviso', [OnboardingController::class, 'acceptPrivacy'])->name('participant.privacy.accept');
});

// --- Chatbot público (requiere participante autenticado y onboarded) -----------
Route::middleware(['participant.auth', 'participant.onboarded'])->group(function () {
    Route::get('/chatbot-catequesis', [CatequesisChatController::class, 'show'])->name('chatbot.show');
    Route::post('/chatbot-catequesis/preguntar', [CatequesisChatController::class, 'chat'])->name('chatbot.chat');
});

// --- Autenticación de administradores (login mínimo basado en sesión) ----------
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- Panel administrativo de NicenoBot -----------------------------------------
Route::middleware('nicenito.admin')
    ->prefix('admin/nicenito')
    ->name('admin.nicenito.')
    ->group(function () {
        Route::redirect('/', '/admin/nicenito/dashboard');
        Route::get('/dashboard', [NicenoBotDashboardController::class, 'index'])->name('dashboard');

        Route::get('/contenidos', [NicenoBotContentController::class, 'index'])->name('contenidos.index');
        Route::get('/contenidos/crear', [NicenoBotContentController::class, 'create'])->name('contenidos.create');
        Route::post('/contenidos', [NicenoBotContentController::class, 'store'])->name('contenidos.store');
        Route::get('/contenidos/{content}/editar', [NicenoBotContentController::class, 'edit'])->name('contenidos.edit');
        Route::put('/contenidos/{content}', [NicenoBotContentController::class, 'update'])->name('contenidos.update');
        Route::delete('/contenidos/{content}', [NicenoBotContentController::class, 'destroy'])->name('contenidos.destroy');

        Route::match(['get', 'post'], '/contenidos/{content}/vista-previa', [NicenoBotContentController::class, 'preview'])->name('contenidos.preview');
        Route::post('/contenidos/{content}/publicar', [NicenoBotContentController::class, 'publish'])->name('contenidos.publish');
        Route::post('/contenidos/{content}/archivar', [NicenoBotContentController::class, 'archive'])->name('contenidos.archive');
        Route::post('/contenidos/{content}/duplicar', [NicenoBotContentController::class, 'duplicate'])->name('contenidos.duplicate');

        // Participantes
        Route::get('/participantes', [ParticipantController::class, 'index'])->name('participantes.index');
        Route::get('/participantes/crear', [ParticipantController::class, 'create'])->name('participantes.create');
        Route::post('/participantes', [ParticipantController::class, 'store'])->name('participantes.store');
        Route::get('/participantes/{participante}/editar', [ParticipantController::class, 'edit'])->name('participantes.edit');
        Route::put('/participantes/{participante}', [ParticipantController::class, 'update'])->name('participantes.update');
        Route::delete('/participantes/{participante}', [ParticipantController::class, 'destroy'])->name('participantes.destroy');
        Route::get('/participantes/{participante}/credenciales', [ParticipantController::class, 'credentials'])->name('participantes.credentials');
        Route::post('/participantes/{participante}/estado', [ParticipantController::class, 'toggleActive'])->name('participantes.toggle');
        Route::post('/participantes/{participante}/regenerar-pin', [ParticipantController::class, 'regeneratePin'])->name('participantes.regenerate-pin');
        Route::post('/participantes/{participante}/regenerar-codigo', [ParticipantController::class, 'regenerateCode'])->name('participantes.regenerate-code');

        // Preguntas
        Route::get('/preguntas', [NicenoBotQuestionController::class, 'index'])->name('preguntas.index');
        Route::get('/preguntas/{pregunta}', [NicenoBotQuestionController::class, 'show'])->name('preguntas.show');
        Route::put('/preguntas/{pregunta}/seguimiento', [NicenoBotQuestionController::class, 'updateFollowUp'])->name('preguntas.follow-up');

        // Perfil del usuario de backoffice
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    });
