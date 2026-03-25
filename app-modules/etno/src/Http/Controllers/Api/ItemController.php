<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Metafori\Etno\Http\Requests\Api\ItemIndexRequest;
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
        $sortQuery = $request->validated('sort');
        $sorts = $sortQuery ? explode(',', $sortQuery) : [];

        $items = $this->repository->paginate($filters, $sorts);

        return ItemResource::collection($items);
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
