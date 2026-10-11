<?php

namespace App\Modules\Institutions\Configuration;

class Relations
{
    public static function definitions(): array
    {
        return ['users' => ['label' => 'Usuarios de la institución',
            'permission' => Permissions::RELATED_USERS, 'columns' => ['name', 'email', 'job_title'],
            'actions' => ['open'], 'per_page' => 15]];
    }
}
