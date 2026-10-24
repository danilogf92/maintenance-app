<?php
namespace App\Modules\MaintenancePlans\Models;
use Illuminate\Database\Eloquent\Model;
class MaintenanceVisit extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['scheduled_for' => 'date', 'lock_version' => 'integer']; }
    public function completedServices()
    {
        return $this->hasManyThrough(\App\Modules\Services\Models\Service::class, \App\Modules\Tickets\Models\Ticket::class,
            'maintenance_visit_id','ticket_id','id','id')->where('services.status','completed');
    }
    public function plan() { return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id'); }
    public function technician() { return $this->belongsTo(\App\Models\User::class); }
    public function ticket() { return $this->hasOne(\App\Modules\Tickets\Models\Ticket::class); }
}
