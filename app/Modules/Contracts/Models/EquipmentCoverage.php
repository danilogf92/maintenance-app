<?php

namespace App\Modules\Contracts\Models;

use App\Modules\Equipment\Models\Equipment;
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use Illuminate\Database\Eloquent\Model;

class EquipmentCoverage extends Model
{
    protected $table = 'contract_equipment_coverages';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['lock_version' => 'integer'];
    }

    public function contract()
    {
        return $this->belongsTo(MaintenanceContract::class, 'maintenance_contract_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function plans()
    {
        return $this->hasMany(MaintenancePlan::class, 'contract_coverage_id');
    }
}
