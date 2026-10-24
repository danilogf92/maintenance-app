<?php

namespace App\Modules\Institutions\Configuration;

class Relations
{
    public static function definitions(): array
    {
        return ['contacts' => ['label' => 'Contactos', 'permission' => 'contacts.view', 'columns' => ['name', 'category'], 'actions' => ['open'], 'per_page' => 15], 'users' => ['label' => 'Usuarios de la institución',
            'permission' => Permissions::RELATED_USERS, 'columns' => ['name', 'email', 'job_title'],
            'actions' => ['open'], 'per_page' => 15]];
    }
}
