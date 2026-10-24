<?php
namespace App\Modules\DataManagement\Support;
use Illuminate\Support\Str;
class SourceName
{
    public static function normalize(string $name): string
    {
        $name = preg_replace('/^\d+[\s_-]*/u', '', Str::lower(Str::ascii($name)));
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $name));
    }
    public static function key(array $source): string { return hash('sha256', $source['folder']); }
}
