<?php

namespace App\Http\Controllers;

use App\Services\CatequesisChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CatequesisChatController extends Controller
{
    public function __construct(
        private readonly CatequesisChatService $catequesisChatService,
    ) {
    }

    public function show(): View
    {
        Log::info('niceno.page_visit', ['ip' => request()->ip()]);

        return view('catequesis.chatbot');
    }

    public function chat(Request $request): JsonResponse
    {
        Log::info('niceno.chat_request', [
            'ip'      => $request->ip(),
            'message' => $request->input('message'),
        ]);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:200'],
        ], [
            'message.required' => 'Escribe una pregunta para continuar.',
            'message.max'      => 'Tu pregunta no puede superar los 200 caracteres.',
        ]);

        $result = $this->catequesisChatService->respond($validated['message']);

        Log::info('niceno.chat_response', [
            'answer_length' => mb_strlen($result['answer']),
            'has_sources'   => ! empty($result['sources']),
        ]);

        return response()->json($result);
    }
}
