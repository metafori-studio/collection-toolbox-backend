<?php

namespace Metafori\Art\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Metafori\Art\Models\Collection;

class CollectionRepository
{
    public function findOrFail(int $id): Collection
    {
        return Collection::query()
            ->published()
            ->withCount('artworks')
            ->withDatespan()
            ->with(['category', 'previewArtworks'])
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, 'asc'|'desc'>  $sorts
     * @return LengthAwarePaginator<int, Collection>
     */
    public function paginate(
        array $filters,
        array $sorts,
        string $locale,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = Collection::query()
            ->published()
            ->withCount('artworks')
            ->with(['category', 'previewArtworks']);

        if (\array_key_exists('is_featured', $filters)) {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }

        foreach ($sorts as $field => $dir) {
            $query->orderBy($this->resolveSortField($field, $locale), $dir);
        }

        $query->orderBy('id');

        return $query->paginate($perPage);
    }

    protected function resolveSortField(string $field, string $locale): string
    {
        if ((new Collection)->isTranslatableAttribute($field)) {
            return "{$field}->{$locale}";
        }

        return $field;
    }
}
