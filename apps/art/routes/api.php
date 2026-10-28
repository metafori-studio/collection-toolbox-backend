<?php

use Illuminate\Support\Facades\Route;
use Metafori\Art\Http\Controllers\Api\ArtworkController;

Route::get('artworks', [ArtworkController::class, 'index'])->name('artworks.index');
Route::get('artworks/{artwork}', [ArtworkController::class, 'show'])->name('artworks.show');
