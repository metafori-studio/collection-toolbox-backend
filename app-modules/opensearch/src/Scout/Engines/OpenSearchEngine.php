<?php

namespace Metafori\Opensearch\Scout\Engines;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use OpenSearch\Client as OpenSearchClient;

class OpenSearchEngine extends Engine
{
    protected OpenSearchClient $client;

    protected bool $softDelete;

    public function __construct(OpenSearchClient $client, bool $softDelete = false)
    {
        $this->client = $client;
        $this->softDelete = $softDelete;
    }

    public function update($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $models->first()->searchableAs();

        if ($this->usesSoftDelete($models->first()) && $this->softDelete) {
            $models->each->pushSoftDeleteMetadata();
        }

        $body = [];

        foreach ($models as $model) {
            if (empty($searchableData = $model->toSearchableArray())) {
                continue;
            }

            $body[] = [
                'index' => [
                    '_index' => $index,
                    '_id' => $model->getScoutKey(),
                ],
            ];

            $body[] = array_merge(
                $searchableData,
                $model->scoutMetadata()
            );
        }

        if (empty($body)) {
            return;
        }

        $this->client->bulk(['body' => $body]);
    }

    public function delete($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $models->first()->searchableAs();
        $body = [];

        foreach ($models as $model) {
            $body[] = [
                'delete' => [
                    '_index' => $index,
                    '_id' => $model->getScoutKey(),
                ],
            ];
        }

        $this->client->bulk(['body' => $body]);
    }

    public function search(Builder $builder): mixed
    {
        return $this->performSearch($builder, array_filter([
            'size' => $builder->limit,
        ]));
    }

    public function paginate(Builder $builder, $perPage, $page): mixed
    {
        $from = ($page - 1) * $perPage;

        return $this->performSearch($builder, [
            'from' => $from,
            'size' => $perPage,
        ]);
    }

    protected function performSearch(Builder $builder, array $options = []): mixed
    {
        $query = [
            'bool' => [
                'must' => [
                    ['query_string' => ['query' => $builder->query === '' ? '*' : $builder->query]],
                ],
            ],
        ];

        if ($this->filters($builder)) {
            $query['bool']['filter'] = $this->filters($builder);
        }

        $sort = $this->buildSortFromOrderByClauses($builder);

        $params = [
            'index' => $builder->index ?: $builder->model->searchableAs(),
            'body' => array_merge([
                'query' => $query,
            ], $sort ? ['sort' => $sort] : []),
        ];

        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }

        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }

        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this->client,
                $builder->query,
                $params
            );
        }

        return $this->client->search($params);
    }

    protected function filters(Builder $builder): array
    {
        $must = collect($builder->wheres)->map(function ($value, $key) {
            return ['term' => [$key => $value]];
        })->values()->all();

        foreach ($builder->whereIns as $key => $values) {
            $must[] = ['terms' => [$key => $values]];
        }

        $mustNot = [];
        foreach ($builder->whereNotIns as $key => $values) {
            $mustNot[] = ['terms' => [$key => $values]];
        }

        if (empty($must) && empty($mustNot)) {
            return [];
        }

        return [
            'bool' => array_filter([
                'must' => $must,
                'must_not' => $mustNot,
            ]),
        ];
    }

    protected function buildSortFromOrderByClauses(Builder $builder): ?array
    {
        if (empty($builder->orders)) {
            return null;
        }

        return collect($builder->orders)->map(function ($order) {
            return [$order['column'] => $order['direction']];
        })->all();
    }

    public function mapIds($results): Collection
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    public function map(Builder $builder, $results, $model): EloquentCollection
    {
        if ($this->getTotalCount($results) === 0) {
            return $model->newCollection();
        }

        $objectIds = $this->mapIds($results)->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->getScoutModelsByIds($builder, $objectIds)
            ->filter(function (Model $model) use ($objectIds) {
                return in_array($model->getScoutKey(), $objectIds);
            })->sortBy(function (Model $model) use ($objectIdPositions) {
                return $objectIdPositions[$model->getScoutKey()];
            })->values();
    }

    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        if ($this->getTotalCount($results) === 0) {
            return LazyCollection::make($model->newCollection());
        }

        $objectIds = $this->mapIds($results)->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->queryScoutModelsByIds($builder, $objectIds)
            ->cursor()
            ->filter(function (Model $model) use ($objectIds) {
                return in_array($model->getScoutKey(), $objectIds);
            })->sortBy(function (Model $model) use ($objectIdPositions) {
                return $objectIdPositions[$model->getScoutKey()];
            })->values();
    }

    public function getTotalCount($results): int
    {
        return $results['hits']['total']['value'] ?? 0;
    }

    public function flush($model): void
    {
        $this->client->indices()->delete([
            'index' => $model->searchableAs(),
            'ignore_unavailable' => true,
        ]);
    }

    public function createIndex($name, array $options = []): mixed
    {
        $params = ['index' => (string) $name];

        if (! empty($options)) {
            $params['body'] = $options;
        }

        return $this->client->indices()->create($params);
    }

    public function deleteIndex($name): mixed
    {
        return $this->client->indices()->delete(['index' => (string) $name]);
    }

    protected function usesSoftDelete(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}
