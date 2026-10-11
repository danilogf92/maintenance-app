<?php

namespace App\Filament\Resources\UiColors;

use App\Models\UiColorSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UiColorResource extends Resource
{
    protected static ?string $model = UiColorSetting::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'Colores';

    protected static ?string $modelLabel = 'Colores de botones';

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
            ColorPicker::make('primary')->label('Primario / confirmar')->required()->rules(['regex:/^#[0-9A-Fa-f]{6}$/']),
            ColorPicker::make('success')->label('Éxito / guardado')->required()->rules(['regex:/^#[0-9A-Fa-f]{6}$/']),
            ColorPicker::make('danger')->label('Peligro / eliminar / cerrar')->required()->rules(['regex:/^#[0-9A-Fa-f]{6}$/']),
            ColorPicker::make('secondary')->label('Cancelar / secundario')->required()->rules(['regex:/^#[0-9A-Fa-f]{6}$/']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('primary')->label('Primario'),
            TextColumn::make('success')->label('Éxito'),
            TextColumn::make('danger')->label('Peligro'),
            TextColumn::make('secondary')->label('Secundario'),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUiColors::route('/'),
            'edit' => Pages\EditUiColors::route('/{record}/edit'),
        ];
    }
}
