<?php

namespace App\Modules\ReportTemplates\Rendering;

class SignatureRenderer
{
    /** Signature bytes belong to this report; no reusable signature is stored in templates. */
    public static function image(array $data, string $source): ?string
    {
        if (! in_array($source, ['technician', 'recipient'], true)) { return null; }
        $image = data_get($data, 'signatures.'.$source.'.image');
        if (! is_string($image) || strlen($image) > 2800000 || ! preg_match('~^data:image/(png|jpeg|webp);base64,([A-Za-z0-9+/=]+)$~', $image, $match)) { return null; }
        $bytes = base64_decode($match[2], true);
        if ($bytes === false || strlen($bytes) > 2 * 1024 * 1024) { return null; }
        $info = @getimagesizefromstring($bytes);
        if (! $info || $info['mime'] !== 'image/'.$match[1]) { return null; }

        return $image;
    }
}
