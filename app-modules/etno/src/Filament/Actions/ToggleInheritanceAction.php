<?php

namespace Metafori\Etno\Filament\Actions;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class ToggleInheritanceAction extends Action
{
    protected string $attributeName;

    public function attributeName(string $name): static
    {
        $this->attributeName = $name;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.actions.toggle_inheritance'))
            ->iconButton()
            ->color($this->toggleColor(...))
            ->icon($this->toggleIcon(...))
            ->tooltip($this->toggleTooltip(...))
            ->action($this->toggleInheritance(...));
    }

    public function toggleColor(): string|array
    {
        return $this->isInherited() ? Color::Gray : 'primary';
    }

    public function toggleIcon(): Heroicon
    {
        return $this->isInherited() ? Heroicon::LockClosed : Heroicon::LockOpen;
    }

    public function toggleTooltip(): string
    {
        return $this->isInherited() ? __('etno::ui.tooltips.override') : __('etno::ui.tooltips.inherit');
    }

    public function isInherited(): bool
    {
        return (bool) $this->evaluate(fn (Get $get) => static::isInheritedState($get, $this->attributeName));
    }

    public function toggleInheritance(): void
    {
        if ($this->isInherited()) {
            $this->markAsOverridden();
        } else {
            $this->markAsInherited();
        }
    }

    public function markAsOverridden(): void
    {
        $this->evaluate(function (Get $get, Set $set) {
            $overrides = (array) ($get('document_overrides') ?? []);
            $overrides = array_unique([...$overrides, $this->attributeName]);
            $set('document_overrides', array_values($overrides));
        });
    }

    public function markAsInherited(): void
    {
        $this->evaluate(function (Get $get, Set $set) {
            $overrides = (array) ($get('document_overrides') ?? []);
            $overrides = array_diff($overrides, [$this->attributeName]);
            $set('document_overrides', array_values($overrides));
        });
    }

    public static function isInheritedState(Get $get, string $name): bool
    {
        $overrides = (array) ($get('document_overrides') ?? []);

        return ! in_array($name, $overrides, true);
    }
}
