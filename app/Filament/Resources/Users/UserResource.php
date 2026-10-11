<?php

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Modules\User\UserAccess;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Usuarios y acceso';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('users.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        $actor = auth()->user();

        return static::canViewAny() && ($actor->hasRole('SuperAdmin') || ! app(UserAccess::class)->isAdministrative($record));
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
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
            Select::make('institution_id')->label('Institución')->relationship('institution', 'name')->required()->searchable()->preload(),
            TextInput::make('identification')->label('Identificación')->maxLength(255),
            TextInput::make('phone')->label('Teléfono')->maxLength(255),
            TextInput::make('job_title')->label('Cargo')->maxLength(255),
            TextInput::make('password')->label('Contraseña')->password()->minLength(12)
                ->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state)),
            Select::make('role_ids')->label('Roles')->multiple()->searchable()->preload()->default([])
                ->options(fn () => app(UserAccess::class)->rolesFor(auth()->user())->pluck('name', 'id')),
            Toggle::make('is_active')->label('Activo')->default(true),
            Toggle::make('can_access_admin')->label('Acceso administrativo')->default(false)
                ->helperText('También requiere el permiso panel.access.')->disabled(fn () => ! auth()->user()->hasRole('SuperAdmin'))->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nombre')->searchable(), TextColumn::make('email')->label('Correo')->searchable(),
            TextColumn::make('institution.name')->label('Institución'), TextColumn::make('roles.name')->label('Roles')->badge(),
            IconColumn::make('is_active')->label('Activo')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListUsers::route('/'), 'create' => Pages\CreateUser::route('/create'), 'edit' => Pages\EditUser::route('/{record}/edit')];
    }
}
