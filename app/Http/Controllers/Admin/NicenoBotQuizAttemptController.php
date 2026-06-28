<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NicenoBotContent;
use App\Models\NicenoBotQuizAttempt;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NicenoBotQuizAttemptController extends Controller
{
    public function index(Request $request): View
    {
        $attempts = NicenoBotQuizAttempt::query()
            ->with(['participant', 'content'])
            ->when($request->filled('participant_id'), fn ($q) => $q->where('participant_id', $request->integer('participant_id')))
            ->when($request->filled('group'), fn ($q) => $q->whereHas('participant', fn ($p) => $p->where('group_name', $request->string('group'))))
            ->when($request->filled('nicenito_content_id'), fn ($q) => $q->where('nicenito_content_id', $request->integer('nicenito_content_id')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date('date')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.nicenito.quiz.index', [
            'attempts' => $attempts,
            'participants' => Participant::query()->orderBy('full_name')->get(['id', 'full_name']),
            'weeklyContents' => NicenoBotContent::query()->weekly()->orderByDesc('starts_at')->get(['id', 'title']),
            'groups' => Participant::query()->whereNotNull('group_name')->distinct()->pluck('group_name'),
            'filters' => $request->all(),
        ]);
    }

    public function show(NicenoBotQuizAttempt $intento): View
    {
        $intento->load(['participant', 'content']);

        return view('admin.nicenito.quiz.show', ['attempt' => $intento]);
    }
}
