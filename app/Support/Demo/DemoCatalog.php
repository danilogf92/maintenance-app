<?php

namespace App\Support\Demo;

/** Synthetic values; no customer information or reusable login password. */
final class DemoCatalog
{
    public static function institution(int $index): array
    {
        $cities = ['Quito', 'Guayaquil', 'Cuenca', 'Ambato', 'Loja', 'Manta', 'Ibarra', 'Riobamba', 'Machala'];
        $types = ['company', 'hospital', 'clinic', 'university', 'other'];
        $names = ['Empresa', 'Hospital', 'Clínica', 'Universidad', 'Centro'];
        $sectors = ['Andes', 'Pacífico', 'Central', 'Norte', 'Sur', 'Occidente', 'Valle'];
        $city = $cities[($index * 7) % count($cities)];

        return ['name' => $names[$index % 5].' '.$sectors[$index % 7].' '.$city.' '.sprintf('%03d', $index),
            'code' => sprintf('DEMO-%04d', $index), 'type' => $types[$index % 5], 'city' => $city,
            'contact_email' => 'institution.'.$index.'@example.test',
            'contact_phone' => sprintf('02%07d', $index)];
    }

    public static function user(int $index, int $institutionId, string $passwordHash): array
    {
        $names = ['Ana', 'Luis', 'Sofía', 'Mateo', 'Valeria', 'Andrés', 'Camila', 'Daniel'];
        $surnames = ['Mendoza', 'Vega', 'Ríos', 'Torres', 'Castro', 'Morales', 'Cruz'];
        $jobs = ['Ingeniería', 'Administración', 'Compras', 'Mantenimiento', 'Ventas', 'Dirección'];

        return ['name' => $names[$index % 8].' '.$surnames[$index % 7].' '.sprintf('%03d', $index),
            'email' => 'user.'.$index.'@example.test', 'institution_id' => $institutionId,
            'job_title' => $jobs[$index % 6], 'phone' => sprintf('09%08d', $index),
            'identification' => sprintf('DEMO-%06d', $index), 'password' => $passwordHash,
            'is_active' => $index % 5 !== 0, 'can_access_admin' => false];
    }
}
