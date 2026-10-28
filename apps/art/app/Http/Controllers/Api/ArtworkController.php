<?php

namespace Metafori\Art\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Metafori\Art\Http\Requests\Api\ArtworkAggregationsRequest;
use Metafori\Art\Http\Requests\Api\ArtworkIndexRequest;
use Metafori\Art\Http\Requests\Api\ArtworkSearchRequest;
use Metafori\Art\Http\Resources\ArtworkResource;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Repositories\ArtworkRepository;

class ArtworkController extends Controller
{
    public function __construct(
        protected readonly ArtworkRepository $repository,
    ) {}

    public function index(ArtworkIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated('filter', []);
        $sorts = $request->sorts();
        if ($sorts === []) {
            $sorts = ['id' => 'desc'];
        }
        $locale = app()->getLocale();
        $perPage = (int) $request->input('per_page', 15);

        $artworks = $this->repository->paginate($filters, $sorts, $locale, $perPage);

        return ArtworkResource::collection($artworks);
    }

    public function search(ArtworkSearchRequest $request): AnonymousResourceCollection
    {
        $query = (string) $request->string('q');
        $size = (int) $request->integer('size', 10);
        $locale = app()->getLocale();

        $artworks = $this->repository->search($query, $size, $locale);

        return ArtworkResource::collection($artworks);
    }

    public function aggregations(ArtworkAggregationsRequest $request): JsonResponse
    {
        $filters = $request->validated('filter', []);

        $aggregations = $this->repository->aggregations($filters, size: 100);

        return response()->json([
            'data' => $aggregations,
        ]);
    }

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
