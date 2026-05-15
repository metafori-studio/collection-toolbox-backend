<?php

use Illuminate\Support\Facades\Route;
use Metafori\Etno\Http\Controllers\DocumentController;

Route::get('documents/{id}', DocumentController::class)
    ->name('etno.documents.show');
