<?php

namespace Metafori\Core\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Core\Filament\Resources\OrganizationResource\Pages;
use Metafori\Core\Filament\Resources\OrganizationResource\Schemas\OrganizationForm;
use Metafori\Core\Filament\Resources\OrganizationResource\Tables\OrganizationTable;
use Metafori\Core\Models\Organization;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';

    public static function getModelLabel(): string
    {
        return __('core::ui.resources.organization.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('core::ui.resources.organization.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('core::ui.resources.organization.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
