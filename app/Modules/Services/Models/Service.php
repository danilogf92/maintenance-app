<?php

namespace App\Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['service_date' => 'date'];
    }

    public function fieldEngineer() { return $this->belongsTo(\App\Modules\FieldEngineers\Models\FieldEngineer::class); }
    public function ninoxImport() { return $this->hasOne(NinoxServiceImport::class); }
    public function documents() { return $this->hasMany(ServiceDocument::class); }

    public function ticket()
    {
        return $this->belongsTo(\App\Modules\Tickets\Models\Ticket::class, 'ticket_id');
    }

    public function technician()
    {
        return $this->belongsTo(\App\Models\User::class, 'technician_id');
    }

    public function activities()
    {
        return $this->hasMany(\App\Modules\Activities\Models\Activity::class, 'service_id');
    }

    public function protocolApplications()
    {
        return $this->hasMany(\App\Modules\MaintenanceProtocols\Models\ProtocolApplication::class, 'service_id');
    }

    public function reports()
    {
        return $this->hasMany(\App\Modules\TechnicalReports\Models\TechnicalReport::class, 'service_id');
    }

}
