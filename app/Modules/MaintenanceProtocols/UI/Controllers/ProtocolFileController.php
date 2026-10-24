<?php
namespace App\Modules\MaintenanceProtocols\UI\Controllers;
use App\Modules\MaintenanceProtocols\Configuration\Permissions;
use App\Modules\MaintenanceProtocols\Models\ProtocolFile;
use Illuminate\Support\Facades\Storage;
class ProtocolFileController
{
    public function __invoke(ProtocolFile $file)
    {
        Permissions::authorize();
        abort_unless(preg_match('~^maintenance-protocols/images/[a-f0-9]{64}\.(png|jpg|jpeg|webp|pdf)$~', $file->path), 404);
        $disk = Storage::disk('local'); abort_unless($disk->exists($file->path), 404);
        return response()->file($disk->path($file->path), ['Content-Type' => $file->mime,
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'self'", 'Referrer-Policy' => 'same-origin']);
    }
}
