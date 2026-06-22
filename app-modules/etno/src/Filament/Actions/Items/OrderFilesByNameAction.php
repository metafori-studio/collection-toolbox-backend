<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;

class OrderFilesByNameAction extends Action
{
    protected ?string $fileStatePath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.actions.order_by_name'))
            ->icon(Heroicon::BarsArrowDown)
            ->hidden(fn (?array $state): bool => \count($state ?? []) < 2)
            ->action(function (Repeater $component) {
                $state = $component->getState();
                uasort($state, function ($a, $b) {
                    $path = $this->fileStatePath;

                    $fileA = $path ? Arr::get($a, $path) : $a;
                    $fileB = $path ? Arr::get($b, $path) : $b;

                    if ($fileA === null || $fileB === null) {
                        return 0;
                    }

                    return strnatcasecmp(
                        $fileA->getClientOriginalName(),
                        $fileB->getClientOriginalName()
                    );
                });

                $component->state($state);
            });
    }

    public function fileStatePath(?string $path): static
    {
        $this->fileStatePath = $path;

        return $this;
    }

    public function getFileStatePath(): ?string
    {
        return $this->fileStatePath;
    }
}
