<?php

use App\Http\Controllers\CatequesisChatController;
use Illuminate\Support\Facades\Route;

Route::post('/catequesis/chat', [CatequesisChatController::class, 'chat']);
