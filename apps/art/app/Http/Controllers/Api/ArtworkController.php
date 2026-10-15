<?php

namespace Metafori\Art\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Metafori\Art\Http\Resources\ArtworkResource;
use Metafori\Art\Models\Artwork;

class ArtworkController extends Controller
{
    public function show(Artwork $artwork): ArtworkResource
    {
        $artwork->loadMissing([
            'authors.person',
            'collections' => fn ($query) => $query->withCount('artworks'),
            'media',
        ]);

        return ArtworkResource::make($artwork);
    }
}
