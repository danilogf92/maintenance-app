<?php
namespace App\Modules\MaintenancePlans\Configuration;
class Rules
{
    public static function make(): array
    {
        return ['name'=>'required|string|max:160', 'equipment_id'=>'required|integer|exists:equipment,id',
            'technician_id'=>'nullable|integer|exists:users,id', 'service_type'=>'required|string|max:120',
            'first_month'=>'required|date_format:Y-m|after_or_equal:1900-01|before:2201-01',
            'end_month'=>'nullable|date_format:Y-m|after_or_equal:first_month|before:2201-01',
            'first_date'=>'required_if:schedule_precision,day|date_format:Y-m-d|after_or_equal:1900-01-01|before:2201-01-01',
            'end_date'=>'nullable|date_format:Y-m-d|after_or_equal:first_date|before:2201-01-01',
            'interval_unit'=>'required|in:days,weeks,months,years', 'interval_value'=>'required|integer|min:1|max:1440',
            'schedule_precision'=>'required|in:day,month', 'expected_visits'=>'nullable|integer|min:1|max:100000',
            'contract_coverage_id'=>'nullable|integer|exists:contract_equipment_coverages,id',
            'contract_reference'=>'nullable|string|max:160', 'notes'=>'nullable|string|max:5000',
            'active'=>'required|boolean', 'lock_version'=>'required|integer|min:0'];
    }
}
