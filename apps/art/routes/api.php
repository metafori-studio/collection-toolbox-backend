<?php

use Illuminate\Support\Facades\Route;
use Metafori\Art\Http\Controllers\Api\ArtworkController;

Route::get('artworks', [ArtworkController::class, 'index'])->name('artworks.index');
Route::get('artworks/aggregations', [ArtworkController::class, 'aggregations'])->name('artworks.aggregations');
Route::get('artworks/{artwork}', [ArtworkController::class, 'show'])->name('artworks.show');
