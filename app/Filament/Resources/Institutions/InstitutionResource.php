<?php

namespace App\Filament\Resources\Institutions;

use App\Models\Institution;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static ?string $pluralModelLabel = 'Instituciones';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('institutions.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label('Nombre')->required()->maxLength(255), TextInput::make('code')->label('Código')->required()->unique(ignoreRecord: true)->maxLength(255), Select::make('type')->label('Tipo')->options(['hospital' => 'Hospital', 'clinic' => 'Clínica', 'university' => 'Universidad', 'company' => 'Empresa', 'other' => 'Otro'])]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Nombre')->searchable(), TextColumn::make('code')->label('Código'), TextColumn::make('users_count')->counts('users')->label('Usuarios')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInstitutions::route('/'), 'create' => Pages\CreateInstitution::route('/create'), 'edit' => Pages\EditInstitution::route('/{record}/edit')];
    }
}
