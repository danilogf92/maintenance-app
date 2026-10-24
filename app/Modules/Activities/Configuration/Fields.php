<?php

namespace App\Modules\Activities\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

class Fields
{
    public static function definitions(): array
    {
        return ['service_id' => ['label' => 'Servicio', 'type' => 'services', 'required' => true],
            'description' => ['label' => 'Actividad / hallazgo', 'type' => 'textarea', 'required' => true, 'max' => 5000],
            'result' => ['label' => 'Resultado', 'type' => 'textarea', 'max' => 5000],
            'photos' => ['label' => 'Fotografías', 'type' => 'evidence']];
    }

    public static function names(): array { return array_keys(self::definitions()); }

    public static function make(): array
    {
        return [FormFields::relation('service_id', 'Servicio', 'services'),
            Textarea::make('description')->label('Actividad / hallazgo')->required()->maxLength(5000),
            Textarea::make('result')->label('Resultado')->maxLength(5000),
            FileUpload::make('photos')->label('Fotografías de evidencia')->disk('local')->directory(fn () => 'service-photos/'.auth()->id())
                ->visibility('private')->preventFilePathTampering()->getUploadedFileUsing(fn (string $file, $record) => \App\Modules\Activities\Actions\PrivatePhotos::metadata($file, $record))->multiple()->reorderable()->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                ->maxSize(1024)->maxFiles(4)->previewable(false)->openable(false)->downloadable(false)
                ->helperText('Hasta 4 imágenes de 1 MiB por actividad. Acceso privado desde el servicio; sin URL pública.')];
    }
}
