<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Models\Document;

class IdInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.id'))
            ->required()
            ->maxLength(255)
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: fn (Unique $rule) => $rule->withoutTrashed(),
            )
            ->rule(
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    if (! \is_string($value) || $value === '') {
                        return;
                    }

                    $trashedDocument = Document::onlyTrashed()->find($value);

                    if (! $trashedDocument) {
                        return;
                    }

                    $fail(__('etno::ui.validation.id_trashed'));

                    Notification::make()
                        ->warning()
                        ->title(__('etno::ui.notifications.document_id_trashed.title'))
                        ->body(__('etno::ui.notifications.document_id_trashed.body', ['id' => $trashedDocument->id]))
                        ->actions([
                            Action::make('view_document')
                                ->label(__('etno::ui.actions.view_document'))
                                ->button()
                                ->url(DocumentResource::getUrl('edit', ['record' => $trashedDocument])),
                        ])
                        ->persistent()
                        ->send();
                },
                condition: fn ($record): bool => $record === null,
            )
            ->disabled(fn ($record) => $record !== null);
    }
}
