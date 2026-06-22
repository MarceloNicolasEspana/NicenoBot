<?php

use App\Http\Controllers\CatequesisChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chatbot-catequesis', [CatequesisChatController::class, 'show']);
