<?php
namespace App\Modules\MaintenanceProtocols\Models;
use Illuminate\Database\Eloquent\Model;
class ProtocolFile extends Model
{
    protected $table = 'maintenance_protocol_files';
    protected $guarded = ['id'];
    protected $hidden = ['path'];
    public function step() { return $this->belongsTo(ProtocolStep::class, 'protocol_step_id'); }
}
