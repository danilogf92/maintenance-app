<?php

namespace App\Filament\Resources\BrandSettings;

use App\Models\BrandSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandSettingResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $model = BrandSetting::class;

    protected static ?string $pluralModelLabel = 'Marca de la plataforma';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
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
            TextInput::make('name')->label('Nombre de la plataforma')->required()->maxLength(255),
            FileUpload::make('logo_path')->label('Logo')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(2048)->disk('public')->directory('branding')->visibility('public'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Nombre')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListBrandSettings::route('/'), 'edit' => Pages\EditBrandSetting::route('/{record}/edit')];
    }
}
