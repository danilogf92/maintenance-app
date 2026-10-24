<?php

namespace App\Modules\Activities\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['photos' => 'array', 'protocol_snapshot' => 'encrypted:array', 'protocol_include_in_report' => 'boolean'];
    }

    public function protocolApplication() { return $this->belongsTo(\App\Modules\MaintenanceProtocols\Models\ProtocolApplication::class, 'protocol_application_id'); }
    public function protocolStep() { return $this->belongsTo(\App\Modules\MaintenanceProtocols\Models\ProtocolStep::class, 'protocol_step_id'); }

    public function service()
    {
        return $this->belongsTo(\App\Modules\Services\Models\Service::class, 'service_id');
    }

}
