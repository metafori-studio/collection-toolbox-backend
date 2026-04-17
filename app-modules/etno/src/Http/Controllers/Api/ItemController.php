<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Metafori\Etno\Http\Requests\Api\ItemAggregationsRequest;
use Metafori\Etno\Http\Requests\Api\ItemIndexRequest;
use Metafori\Etno\Http\Requests\Api\ItemSearchRequest;
use Metafori\Etno\Http\Resources\ItemMapPointCollection;
use Metafori\Etno\Http\Resources\ItemResource;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

class ItemController
{
    public function __construct(
        private readonly ItemRepository $repository,
    ) {}

    public function index(ItemIndexRequest $request): ResourceCollection
    {
        $filters = $request->validated('filter', []);
        $sorts = $request->sorts();
        $locale = app()->getLocale();

        $items = $this->repository->paginate($filters, $sorts, $locale);

        return ItemResource::collection($items);
    }

    public function search(ItemSearchRequest $request): ResourceCollection
    {
        $query = $request->string('q');
        $size = $request->integer('size', 10);
        $locale = app()->getLocale();

        $items = $this->repository->search((string) $query, $size, $locale);

        return ItemResource::collection($items);
    }

    public function aggregations(ItemAggregationsRequest $request): JsonResponse
    {
        $filters = $request->validated('filter', []);

        $aggregations = $this->repository->aggregations($filters, size: 1000);

        return response()->json([
            /** @var array<string, array<array{value: string, label: string, count: int}>> */
            'data' => $aggregations,
        ]);
    }

    /**
     * @throws ModelNotFoundException<Item>
     */
    public function show(string $id): ItemResource
    {
        $item = $this->repository->findOrFail($id);

        return new ItemResource($item);
    }

    public function mapPoints(): ResourceCollection
    {
        $mapPoints = $this->repository->mapPoints();

        return new ItemMapPointCollection($mapPoints);
    }
}
