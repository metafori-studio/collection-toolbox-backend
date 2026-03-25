<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;

class TranslationController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'enums' => [
                'AccessRights' => $this->getEnumValues(AccessRights::class),
                'AccrualMethod' => $this->getEnumValues(AccrualMethod::class),
                'CollectionMethod' => $this->getEnumValues(CollectionMethod::class),
                'ExtentUnit' => $this->getEnumValues(ExtentUnit::class),
                'ItemType' => $this->getEnumValues(ItemType::class),
                'Language' => $this->getEnumValues(Language::class),
                'License' => $this->getEnumValues(License::class),
                'ProductionMethod' => $this->getEnumValues(ProductionMethod::class),
            ],
        ]);
    }

    /**
     * @param  class-string<\BackedEnum & \Filament\Support\Contracts\HasLabel>  $enumClass
     * @return array<string, string>
     */
    protected function getEnumValues(string $enumClass): array
    {
        return collect($enumClass::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
