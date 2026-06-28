@extends('admin.layout')

@section('title', 'Intento de quiz · NicenoBot')

@section('content')
    @php
        $questions = array_values($attempt->content?->quiz_questions ?? []);
        $answersByIndex = collect($attempt->answers ?? [])->keyBy('question_index');
    @endphp

    <a href="{{ route('admin.nicenito.quiz.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Volver a intentos</a>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">
                    {{ $attempt->participant?->display_name ?? $attempt->participant?->full_name ?? 'Participante' }}
                </h1>
                <p class="text-sm text-slate-500">
                    {{ $attempt->content?->title ?? 'Contenido' }}
                    · {{ $attempt->created_at->format('d/m/Y H:i') }}
                    @if ($attempt->participant?->group_name) · {{ $attempt->participant->group_name }} @endif
                </p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">
                {{ $attempt->score }} / {{ $attempt->total }}
            </span>
        </div>
    </div>

    <div class="mt-4 space-y-4">
        @foreach ($questions as $i => $q)
            @php
                $answer = $answersByIndex->get($i);
                $selected = $answer['selected_index'] ?? null;
                $isCorrect = $answer['is_correct'] ?? false;
                $correct = $q['correct'] ?? null;
            @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="font-semibold text-slate-800">{{ $i + 1 }}. {{ $q['question'] ?? '' }}</p>
                <ul class="mt-2 space-y-1.5">
                    @foreach (($q['options'] ?? []) as $oi => $option)
                        @php
                            $isChosen = $selected !== null && (int) $selected === (int) $oi;
                            $isRight = $correct !== null && (int) $correct === (int) $oi;
                        @endphp
                        <li class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm
                            {{ $isRight ? 'bg-emerald-50 text-emerald-800' : ($isChosen ? 'bg-rose-50 text-rose-800' : 'text-slate-600') }}">
                            <span class="w-4 text-center">
                                @if ($isRight) ✓ @elseif ($isChosen) ✗ @endif
                            </span>
                            <span>{{ $option }}</span>
                            @if ($isChosen) <span class="text-xs font-semibold">(eligió)</span> @endif
                        </li>
                    @endforeach
                    @if ($selected === null)
                        <li class="px-3 text-xs italic text-slate-400">No respondió esta pregunta.</li>
                    @endif
                </ul>
            </div>
        @endforeach
    </div>
@endsection
