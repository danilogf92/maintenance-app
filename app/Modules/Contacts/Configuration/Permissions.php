<?php
namespace App\Modules\Contacts\Configuration;

class Permissions
{
    public static function allows(string $action = 'view'): bool { return \App\Modules\Workflow\Configuration\Permissions::allows('contacts', $action); }
    public static function authorize(string $action = 'view'): void { \App\Modules\Workflow\Configuration\Permissions::authorize('contacts', $action); }
}
