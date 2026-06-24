<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FollowUpStatus;
use App\Http\Controllers\Controller;
use App\Models\NicenitoContent;
use App\Models\NicenitoQuestion;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NicenitoQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $questions = NicenitoQuestion::query()
            ->with(['participant', 'weeklyContent'])
            ->when($request->filled('participant_id'), fn ($q) => $q->where('participant_id', $request->integer('participant_id')))
            ->when($request->filled('group'), fn ($q) => $q->whereHas('participant', fn ($p) => $p->where('group_name', $request->string('group'))))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date('date')))
            ->when($request->filled('category'), fn ($q) => $q->where('detected_category', $request->string('category')))
            ->when($request->filled('weekly_content_id'), fn ($q) => $q->where('weekly_content_id', $request->integer('weekly_content_id')))
            ->when($request->filled('follow_up_status'), fn ($q) => $q->where('follow_up_status', $request->string('follow_up_status')))
            ->when($request->boolean('needs_human'), fn ($q) => $q->where('needs_human_guidance', true))
            ->when($request->boolean('no_content'), fn ($q) => $q->where('has_weekly_content', false)->where('fixed_contents_count', 0))
            ->when($request->boolean('no_answer'), fn ($q) => $q->where(fn ($w) => $w->whereNull('answer')->orWhere('answer', '')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.nicenito.preguntas.index', [
            'questions' => $questions,
            'participants' => Participant::query()->orderBy('full_name')->get(['id', 'full_name']),
            'weeklyContents' => NicenitoContent::query()->weekly()->orderByDesc('starts_at')->get(['id', 'title']),
            'groups' => Participant::query()->whereNotNull('group_name')->distinct()->pluck('group_name'),
            'filters' => $request->all(),
        ]);
    }

    public function show(NicenitoQuestion $pregunta): View
    {
        $pregunta->load(['participant', 'weeklyContent', 'followUpBy']);

        return view('admin.nicenito.preguntas.show', ['question' => $pregunta]);
    }

    public function updateFollowUp(Request $request, NicenitoQuestion $pregunta): RedirectResponse
    {
        $validated = $request->validate([
            'follow_up_status' => ['required', Rule::enum(FollowUpStatus::class)],
            'follow_up_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pregunta->update([
            'follow_up_status' => $validated['follow_up_status'],
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
            'follow_up_by' => Auth::id(),
        ]);

        return back()->with('status', 'Seguimiento actualizado.');
    }
}
