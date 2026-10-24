<?php
namespace App\Modules\Contacts\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'users', 'sections' => [
            ['title' => 'Datos de la persona', 'fields' => ['name', 'position', 'department', 'active'], 'widths' => ['name' => 'full']]]],
            'communication' => ['label' => 'Comunicación', 'icon' => 'phone', 'sections' => [
                ['title' => 'Canales de contacto', 'fields' => ['email', 'phone', 'extension', 'notes'], 'widths' => ['notes' => 'full']]]],
            'contacts' => ['label' => 'Vínculos', 'icon' => 'building', 'relation' => 'contacts']];
    }
}
