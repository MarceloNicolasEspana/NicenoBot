<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NicenitoContentStatus;
use App\Enums\NicenitoContentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\NicenitoContentRequest;
use App\Models\NicenitoContent;
use App\Services\GeminiModelService;
use App\Services\NicenitoContentContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NicenitoContentController extends Controller
{
    public function index(Request $request): View
    {
        $contents = NicenitoContent::query()
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

    public function create(Request $request): View
    {
        $content = new NicenitoContent([
            'type' => $request->string('type')->value() === NicenitoContentType::Fixed->value
                ? NicenitoContentType::Fixed
                : NicenitoContentType::Weekly,
            'status' => NicenitoContentStatus::Draft,
        ]);

        return view('admin.nicenito.contenidos.form', [
            'content' => $content,
            'mode' => 'create',
        ]);
    }

    public function store(NicenitoContentRequest $request): RedirectResponse
    {
        $content = new NicenitoContent($request->validated());
        $content->created_by = Auth::id();
        $content->save();

        return redirect()
            ->route('admin.nicenito.contenidos.edit', $content)
            ->with('status', 'Contenido creado correctamente.');
    }

    public function edit(NicenitoContent $content): View
    {
        return view('admin.nicenito.contenidos.form', [
            'content' => $content,
            'mode' => 'edit',
        ]);
    }

    public function update(NicenitoContentRequest $request, NicenitoContent $content): RedirectResponse
    {
        $content->update($request->validated());

        return redirect()
            ->route('admin.nicenito.contenidos.edit', $content)
            ->with('status', 'Contenido actualizado correctamente.');
    }

    public function destroy(NicenitoContent $content): RedirectResponse
    {
        $content->delete();

        return redirect()
            ->route('admin.nicenito.contenidos.index')
            ->with('status', 'Contenido eliminado.');
    }

    public function publish(NicenitoContent $content): RedirectResponse
    {
        // Acción rápida desde el listado: publica un contenido ya guardado.
        if ($content->type === NicenitoContentType::Weekly) {
            if ($content->starts_at === null || $content->ends_at === null) {
                return back()->with('error', 'El contenido semanal necesita fechas antes de publicarse.');
            }

            if (NicenitoContent::hasPublishedWeeklyOverlap($content->starts_at, $content->ends_at, $content->id)) {
                return back()->with('error', 'No se puede publicar: hay otro contenido semanal publicado en ese rango de fechas.');
            }
        }

        $content->update(['status' => NicenitoContentStatus::Published]);

        return back()->with('status', 'Contenido publicado.');
    }

    public function archive(NicenitoContent $content): RedirectResponse
    {
        $content->update(['status' => NicenitoContentStatus::Archived]);

        return back()->with('status', 'Contenido archivado.');
    }

    public function duplicate(NicenitoContent $content): RedirectResponse
    {
        $copy = $content->replicate();
        $copy->status = NicenitoContentStatus::Draft;
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
        NicenitoContent $content,
        NicenitoContentContextService $contextService,
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
