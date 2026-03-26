<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Metafori\Etno\Http\Requests\Api\ItemAggregationsRequest;
use Metafori\Etno\Http\Requests\Api\ItemIndexRequest;
use Metafori\Etno\Http\Resources\ItemMapPointCollection;
use Metafori\Etno\Http\Resources\ItemResource;
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

        $items = $this->repository->paginate($filters, $sorts);

        return ItemResource::collection($items);
    }

    public function aggregations(ItemAggregationsRequest $request): JsonResponse
    {
        $filters = $request->validated('filter', []);

        $aggregations = $this->repository->aggregations($filters);

        return response()->json([
            /** @var array<string, array<array{value: string, label: string, count: int}>> */
            'data' => $aggregations,
        ]);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Metafori\Etno\Models\Item>
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
