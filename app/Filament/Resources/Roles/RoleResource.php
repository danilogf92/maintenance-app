<?php

namespace App\Filament\Resources\Roles;

use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Usuarios y acceso';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $pluralModelLabel = 'Roles';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
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

    public static function canEdit($record): bool
    {
        return static::canViewAny() && $record->name !== 'SuperAdmin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label('Nombre')->required()->unique(ignoreRecord: true)->maxLength(255), Select::make('permissions')->label('Permisos')->relationship('permissions', 'name')->multiple()->preload()->searchable()]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Nombre'), TextColumn::make('permissions.name')->label('Permisos')->badge()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRoles::route('/'), 'create' => Pages\CreateRole::route('/create'), 'edit' => Pages\EditRole::route('/{record}/edit')];
    }
}
