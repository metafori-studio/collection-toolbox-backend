<?php

namespace Metafori\Art\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Metafori\Art\Models\Artwork;
use OpenSearch\Client;

class ArtworkRepository
{
    public function findOrFail(string $slug): Artwork
    {
        return Artwork::query()
            ->with([
                'authors.person',
                'collections' => fn ($query) => $query->withCount('artworks'),
                'media',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, 'asc'|'desc'>  $sorts
     */
    public function paginate(array $filters, array $sorts, string $locale, int $perPage = 15): LengthAwarePaginator
    {
        $creationDateFilter = $this->buildDateFilter(
            Arr::pull($filters, 'creation_date_from'),
            Arr::pull($filters, 'creation_date_to'),
        );

        $authorFilters = Arr::only($filters, ['author.person_id', 'author.label']);
        $filters = Arr::except($filters, ['author.person_id', 'author.label']);

        $query = Artwork::search('*', function (Client $client, string $query, array $params) use ($creationDateFilter, $authorFilters) {
            if ($creationDateFilter) {
                $params['body']['query']['bool']['filter']['bool']['must'][]['range']['creation_date'] = $creationDateFilter;
            }

            if ($authorFilters !== []) {
                $params['body']['query']['bool']['filter']['bool']['must'][] = $this->authorFilter($authorFilters);
            }

            return $client->search($params);
        });

        foreach ($filters as $field => $value) {
            $query->whereIn($field, (array) $value);
        }

        foreach ($sorts as $field => $dir) {
            $query->orderBy($this->resolveSortField($field, $locale), $dir);
        }

        $query->query(function ($eloquentQuery) {
            $eloquentQuery->with([
                'authors.person',
                'collections' => fn ($query) => $query->withCount('artworks'),
                'media',
            ]);
        });

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, array<int, mixed>>  $filters
     * @return array<string, mixed>
     */
    protected function authorFilter(array $filters): array
    {
        return [
            'bool' => [
                'should' => collect($filters)->map(fn (array $values, string $field) => [
                    'terms' => [$field => $values],
                ])->values()->all(),
                'minimum_should_match' => 1,
            ],
        ];
    }

    protected function resolveSortField(string $field, string $locale): string
    {
        if ($field === 'title') {
            return "title.{$locale}.keyword";
        }

        return $field;
    }

    /**
     * @return array<string, string>
     */
    protected function buildDateFilter(mixed $from, mixed $to): array
    {
        return array_filter([
            'gte' => is_numeric($from) ? "{$from}-01-01" : $from,
            'lte' => is_numeric($to) ? "{$to}-12-31" : $to,
        ]);
    }

    public function refreshIndex(): void
    {
        app(Client::class)->indices()->refresh(['index' => (new Artwork)->searchableAs()]);
    }
}
