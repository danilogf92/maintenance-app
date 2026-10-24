<?php

namespace App\Modules\Activities\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PrivatePhotos
{
    public static function image(string $path): string
    {
        if (! preg_match('~^service-photos/[0-9]+/[A-Za-z0-9_-]+\.(png|jpg|jpeg|webp)$~i', $path)) { self::invalid(); }
        $disk = Storage::disk('local');
        if (! $disk->exists($path) || $disk->size($path) > 1024 * 1024) { self::invalid(); }
        $bytes = $disk->get($path); $info = @getimagesizefromstring($bytes);
        if (! $info || ! in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) { self::invalid(); }

        return 'data:'.$info['mime'].';base64,'.base64_encode($bytes);
    }

    public static function validate(array $paths, array $previous): array
    {
        $paths = array_values(array_unique($paths));
        foreach ($paths as $path) {
            if (! in_array($path, $previous, true) && ! str_starts_with($path, 'service-photos/'.auth()->id().'/')) {
                throw ValidationException::withMessages(['photos' => 'No puedes asociar archivos cargados por otra persona.']);
            }
            self::image($path);
        }

        return $paths;
    }

    public static function metadata(string $file, ?\Illuminate\Database\Eloquent\Model $record): ?array
    {
        $previous = $record instanceof \App\Modules\Activities\Models\Activity ? ($record->photos ?? []) : [];
        if (! in_array($file, $previous, true) && ! str_starts_with($file, 'service-photos/'.auth()->id().'/')) { return null; }
        self::image($file); $index = array_search($file, $previous, true);

        return ['name' => basename($file), 'size' => Storage::disk('local')->size($file),
            'type' => Storage::disk('local')->mimeType($file),
            'url' => $index !== false ? route('workflow.photo', ['activity' => $record->id, 'photo' => $index]) : ''];
    }

    private static function invalid(): never
    {
        throw ValidationException::withMessages(['photos' => 'Una fotografía no existe, supera 1 MiB o no es PNG/JPEG/WebP válido.']);
    }
}
