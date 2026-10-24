<?php
namespace App\Modules\MaintenancePlans\Models;
use Illuminate\Database\Eloquent\Model;
class MaintenancePlan extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'first_date' => 'date', 'end_date' => 'date', 'interval_value' => 'integer', 'interval_months' => 'integer', 'lock_version' => 'integer', 'schedule_version' => 'integer', 'interval_value'=>'integer', 'expected_visits'=>'integer']; }
    public function coverage() { return $this->belongsTo(\App\Modules\Contracts\Models\EquipmentCoverage::class, 'contract_coverage_id'); }
    public function equipment() { return $this->belongsTo(\App\Modules\Equipment\Models\Equipment::class); }
    public function technician() { return $this->belongsTo(\App\Models\User::class); }
    public function visits() { return $this->hasMany(MaintenanceVisit::class); }
}
