<?php

namespace Metafori\Art\Http\Controllers\Api;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Metafori\Art\Http\Requests\Api\CollectionArtworksRequest;
use Metafori\Art\Http\Requests\Api\CollectionIndexRequest;
use Metafori\Art\Http\Resources\ArtworkResource;
use Metafori\Art\Http\Resources\CollectionResource;
use Metafori\Art\Repositories\ArtworkRepository;
use Metafori\Art\Repositories\CollectionRepository;

class CollectionController extends Controller
{
    public function __construct(
        protected readonly CollectionRepository $repository,
    ) {}

    public function index(CollectionIndexRequest $request): AnonymousResourceCollection
    {
        $collections = $this->repository->paginate(
            $request->filters(),
            $request->sorts(),
            app()->getLocale(),
            $request->perPage(),
        );

        return CollectionResource::collection($collections);
    }

    public function show(int $collection): CollectionResource
    {
        return CollectionResource::make(
            $this->repository->findOrFail($collection),
        );
    }

    public function artworks(CollectionArtworksRequest $request, int $collection, ArtworkRepository $artworkRepository): AnonymousResourceCollection
    {
        $collection = $this->repository->findOrFail($collection);

        $artworks = $artworkRepository->paginate(
            ['collection.id' => [$collection->id]],
            $request->sorts(),
            app()->getLocale(),
            $request->perPage(),
        );

        return ArtworkResource::collection($artworks);
    }
}
