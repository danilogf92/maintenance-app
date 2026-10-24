<?php
namespace App\Modules\DataManagement\Configuration;
class EquipmentHistories
{
    public static function all(): array
    {
        return [
            'equipment_institution_histories' => ['table' => 'QC', 'equipment' => 'equipment', 'parent' => 'centers', 'date' => 'assigned_date',
                'column' => 'institution_id', 'parent_entity' => 'institutions', 'parent_table' => 'B', 'reverse' => 'equipment_medical_centers_history'],
            'equipment_client_histories' => ['table' => 'RC', 'equipment' => 'equipments', 'parent' => 'clients', 'date' => 'assignation_datr',
                'column' => 'client_id', 'parent_entity' => 'clients', 'parent_table' => 'A', 'reverse' => 'customer_history'],
        ];
    }
}
