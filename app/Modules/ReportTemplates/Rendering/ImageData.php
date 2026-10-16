<?php

namespace App\Modules\ReportTemplates\Rendering;

use Illuminate\Support\Facades\Storage;

class ImageData
{
    public static function fromPublicPath(?string $path): ?string
    {
        if (! $path || ! preg_match('~^(report-assets|branding)/[A-Za-z0-9/_-]+\.(png|jpg|jpeg|webp)$~i', $path)) {
            return null;
        }
        $disk = Storage::disk('public');
        if (! $disk->exists($path) || $disk->size($path) > 2 * 1024 * 1024) {
            return null;
        }
        $bytes = $disk->get($path);
        $info = @getimagesizefromstring($bytes);
        if (! $info || ! in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'])) {
            return null;
        }

        return 'data:'.$info['mime'].';base64,'.base64_encode($bytes);
    }
}
