<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NicenoBotContentStatus;
use App\Enums\NicenoBotContentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\NicenoBotContentRequest;
use App\Models\NicenoBotContent;
use App\Services\GeminiModelService;
use App\Services\NicenoBotContentContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NicenoBotContentController extends Controller
{
    public function index(Request $request): View
    {
        $contents = NicenoBotContent::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.nicenito.contenidos.index', [
            'contents' => $contents,
            'filters' => $request->only(['type', 'status', 'category', 'q']),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $content = new NicenoBotContent([
            'type' => $request->string('type')->value() === NicenoBotContentType::Fixed->value
                ? NicenoBotContentType::Fixed
                : NicenoBotContentType::Weekly,
            'status' => NicenoBotContentStatus::Draft,
        ]);

        if (! $request->ajax()) {
            return redirect()->route('admin.nicenito.contenidos.index');
        }

        return view('admin.nicenito.contenidos._form', ['content' => $content, 'mode' => 'create']);
    }

    public function store(NicenoBotContentRequest $request): RedirectResponse|JsonResponse
    {
        $content = new NicenoBotContent($request->validated());
        $content->created_by = Auth::id();
        $content->save();

        return $this->saved($request, 'Contenido creado correctamente.');
    }

    public function edit(Request $request, NicenoBotContent $content): View|RedirectResponse
    {
        if (! $request->ajax()) {
            return redirect()->route('admin.nicenito.contenidos.index');
        }

        return view('admin.nicenito.contenidos._form', ['content' => $content, 'mode' => 'edit']);
    }

    public function update(NicenoBotContentRequest $request, NicenoBotContent $content): RedirectResponse|JsonResponse
    {
        $content->update($request->validated());

        return $this->saved($request, 'Contenido actualizado correctamente.');
    }

    /**
     * Respuesta tras guardar: JSON (con destino) para peticiones AJAX del modal,
     * o redirección normal para envíos clásicos.
     */
    private function saved(Request $request, string $message): RedirectResponse|JsonResponse
    {
        $url = route('admin.nicenito.contenidos.index');
        $request->session()->flash('status', $message);

        return $request->expectsJson()
            ? response()->json(['redirect' => $url])
            : redirect($url);
    }

    public function destroy(NicenoBotContent $content): RedirectResponse
    {
        $content->delete();

        return redirect()
            ->route('admin.nicenito.contenidos.index')
            ->with('status', 'Contenido eliminado.');
    }

    public function publish(NicenoBotContent $content): RedirectResponse
    {
        // Acción rápida desde el listado: publica un contenido ya guardado.
        if ($content->type === NicenoBotContentType::Weekly) {
            if ($content->starts_at === null || $content->ends_at === null) {
                return back()->with('error', 'El contenido semanal necesita fechas antes de publicarse.');
            }

            if (NicenoBotContent::hasPublishedWeeklyOverlap($content->starts_at, $content->ends_at, $content->id)) {
                return back()->with('error', 'No se puede publicar: hay otro contenido semanal publicado en ese rango de fechas.');
            }
        }

        $content->update(['status' => NicenoBotContentStatus::Published]);

        return back()->with('status', 'Contenido publicado.');
    }

    public function archive(NicenoBotContent $content): RedirectResponse
    {
        $content->update(['status' => NicenoBotContentStatus::Archived]);

        return back()->with('status', 'Contenido archivado.');
    }

    public function duplicate(NicenoBotContent $content): RedirectResponse
    {
        $copy = $content->replicate();
        $copy->status = NicenoBotContentStatus::Draft;
        $copy->title = $content->title.' (copia)';
        $copy->slug = Str::slug($content->title).'-copia-'.Str::random(5);
        $copy->starts_at = null;
        $copy->ends_at = null;
        $copy->created_by = Auth::id();
        $copy->save();

        return redirect()
            ->route('admin.nicenito.contenidos.edit', $copy)
            ->with('status', 'Contenido duplicado como borrador.');
    }

    /**
     * Vista previa: ejecuta el mismo pipeline de recuperación + Gemini pero sin
     * tocar conversaciones reales ni guardar la pregunta del usuario.
     */
    public function preview(
        Request $request,
        NicenoBotContent $content,
        NicenoBotContentContextService $contextService,
        GeminiModelService $gemini,
    ): View {
        $question = $request->string('question')->toString();
        $result = null;
        $context = null;
        $userPrompt = null;

        if ($question !== '') {
            $context = $contextService->build($question);
            $userPrompt = $gemini->buildUserPrompt($question, $context['context_text']);
            $result = $gemini->generate($question, $context['context_text']);
        }

        return view('admin.nicenito.contenidos.preview', [
            'content' => $content,
            'question' => $question,
            'context' => $context,
            'userPrompt' => $userPrompt,
            'systemPrompt' => GeminiModelService::SYSTEM_PROMPT,
            'result' => $result,
        ]);
    }
}
