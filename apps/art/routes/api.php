<?php

use Illuminate\Support\Facades\Route;
use Metafori\Art\Http\Controllers\Api\ArtworkController;
use Metafori\Art\Http\Controllers\Api\CollectionController;

Route::get('artworks', [ArtworkController::class, 'index'])->name('artworks.index');
Route::get('artworks/search', [ArtworkController::class, 'search'])->name('artworks.search');
Route::get('artworks/aggregations', [ArtworkController::class, 'aggregations'])->name('artworks.aggregations');
Route::get('artworks/{artwork}', [ArtworkController::class, 'show'])->name('artworks.show');

Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('collections/{collection}', [CollectionController::class, 'show'])
    ->whereNumber('collection')
    ->name('collections.show');
Route::get('collections/{collection}/artworks', [CollectionController::class, 'artworks'])
    ->whereNumber('collection')
    ->name('collections.artworks');
