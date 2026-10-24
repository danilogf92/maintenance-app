<?php
namespace App\Modules\FieldEngineers\Configuration;
class Permissions
{
    public static function allows(string $action='view'): bool { return \App\Modules\Workflow\Configuration\Permissions::allows('field_engineers',$action); }
    public static function authorize(): void { abort_unless(self::allows(),403); }
}
