<?php

namespace App\Modules\Equipment\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class Fields
{
    public static function definitions(): array
    {
        return ['name' => ['label' => 'Equipo', 'type' => 'text', 'required' => true, 'max' => 160],
            'brand_id' => ['label' => 'Marca', 'type' => 'brands'],
            'equipment_model_id' => ['label' => 'Modelo', 'type' => 'equipment_models'],
            'serial' => ['label' => 'Serie (puede repetirse)', 'type' => 'text', 'max' => 100],
            'status' => ['label' => 'Estado', 'type' => 'status', 'required' => true],
            'client_id' => ['label' => 'Cliente propietario', 'type' => 'clients', 'required' => true],
            'institution_id' => ['label' => 'Institución', 'type' => 'institutions', 'required' => true],
            'location' => ['label' => 'Área / ubicación', 'type' => 'text', 'max' => 160]];
    }

    public static function names(): array { return array_keys(self::definitions()); }

    public static function make(): array
    {
        return [FormFields::relation('client_id', 'Cliente propietario', 'clients'),
            Select::make('institution_id')->label('Institución / ubicación')->required()->searchable()
                ->getSearchResultsUsing(fn (string $search) => \App\Models\Institution::where('name', 'like', '%'.$search.'%')->limit(30)->pluck('name', 'id')->all())
                ->getOptionLabelUsing(fn ($value) => \App\Models\Institution::find($value)?->name),
            TextInput::make('name')->label('Equipo')->required()->maxLength(160),
            Select::make('brand_id')->label('Marca')->searchable()->live()
                ->options(fn () => \App\Modules\Catalogs\Queries\CatalogOptions::get('brands'))
                ->getOptionLabelUsing(fn ($value) => \App\Modules\Brands\Models\Brand::find($value)?->name)
                ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('equipment_model_id', null)),
            Select::make('equipment_model_id')->label('Modelo')->searchable()
                ->options(fn (\Filament\Schemas\Components\Utilities\Get $get) => \App\Modules\Catalogs\Queries\CatalogOptions::get('equipment_models', '', '', (int)$get('brand_id') ?: null))
                ->getOptionLabelUsing(fn ($value) => \App\Modules\EquipmentModels\Models\EquipmentModel::find($value)?->name),
            TextInput::make('serial')->label('Serie (puede repetirse)')->maxLength(100),
            TextInput::make('location')->label('Área / ubicación')->maxLength(160),
            Select::make('status')->label('Estado')->options(self::statuses())->default('operational')->required()];
    }

    public static function statuses(): array
    {
        return ['unknown' => 'Por verificar', 'operational' => 'Operativo', 'maintenance' => 'En mantenimiento', 'out_of_service' => 'Fuera de servicio', 'retired' => 'Retirado'];
    }
}
