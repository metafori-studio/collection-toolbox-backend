<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Metafori\Etno\Http\Resources\ItemMapPointCollection;
use Metafori\Etno\Http\Resources\ItemResource;
use Metafori\Etno\Repositories\ItemRepository;

class ItemController
{
    public function __construct(
        private readonly ItemRepository $repository,
    ) {}

    public function index(): ResourceCollection
    {
        $items = $this->repository->paginate();

        return ItemResource::collection($items);
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
