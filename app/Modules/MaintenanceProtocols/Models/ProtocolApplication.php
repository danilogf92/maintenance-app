<?php
namespace App\Modules\MaintenanceProtocols\Models;
use Illuminate\Database\Eloquent\Model;
class ProtocolApplication extends Model
{
    protected $table = 'service_protocol_applications';
    protected $guarded = ['id'];
    protected function casts(): array { return ['snapshot' => 'encrypted:array']; }
    public function protocol() { return $this->belongsTo(MaintenanceProtocol::class, 'maintenance_protocol_id'); }
    public function activities() { return $this->hasMany(\App\Modules\Activities\Models\Activity::class, 'protocol_application_id'); }
    public function service() { return $this->belongsTo(\App\Modules\Services\Models\Service::class); }
}
