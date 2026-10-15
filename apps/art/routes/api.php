<?php

use Illuminate\Support\Facades\Route;
use Metafori\Art\Http\Controllers\Api\ArtworkController;

Route::get('artworks/{artwork}', [ArtworkController::class, 'show'])->name('artworks.show');
