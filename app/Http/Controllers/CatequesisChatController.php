<?php

namespace App\Http\Controllers;

use App\Services\CatequesisChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatequesisChatController extends Controller
{
    public function __construct(
        private readonly CatequesisChatService $catequesisChatService,
    ) {
    }

    public function show(): View
    {
        return view('catequesis.chatbot');
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ], [
            'message.required' => 'Escribe una pregunta para continuar.',
            'message.max' => 'Tu mensaje no puede superar los 500 caracteres.',
        ]);

        return response()->json(
            $this->catequesisChatService->respond($validated['message'])
        );
    }
}
