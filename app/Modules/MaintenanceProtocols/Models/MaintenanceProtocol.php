<?php
namespace App\Modules\MaintenanceProtocols\Models;
use Illuminate\Database\Eloquent\Model;
class MaintenanceProtocol extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['source_payload', 'source_hash'];
    protected function casts(): array { return ['source_payload' => 'encrypted:array', 'is_obsolete' => 'boolean']; }
    public function steps() { return $this->hasMany(ProtocolStep::class)->orderBy('position')->orderBy('id'); }
    public function applications() { return $this->hasMany(ProtocolApplication::class); }
}
