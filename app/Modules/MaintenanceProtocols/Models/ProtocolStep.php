<?php
namespace App\Modules\MaintenanceProtocols\Models;
use Illuminate\Database\Eloquent\Model;
class ProtocolStep extends Model
{
    protected $table = 'maintenance_protocol_steps';
    protected $guarded = ['id'];
    protected $hidden = ['source_payload', 'source_hash'];
    protected function casts(): array { return ['source_payload' => 'encrypted:array', 'include_in_report' => 'boolean']; }
    public function protocol() { return $this->belongsTo(MaintenanceProtocol::class, 'maintenance_protocol_id'); }
    public function files() { return $this->hasMany(ProtocolFile::class, 'protocol_step_id'); }
}
