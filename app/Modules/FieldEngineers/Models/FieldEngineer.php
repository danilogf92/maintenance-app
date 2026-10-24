<?php
namespace App\Modules\FieldEngineers\Models;
use Illuminate\Database\Eloquent\Model;
class FieldEngineer extends Model
{
    protected $guarded=['id'];
    protected $hidden=['source_payload','source_hash'];
    protected function casts(): array { return ['source_payload'=>'encrypted:array']; }
    public function tickets() { return $this->belongsToMany(\App\Modules\Tickets\Models\Ticket::class,'ticket_field_engineer')->withPivot('source_assignment_id'); }
    public function services() { return $this->hasMany(\App\Modules\Services\Models\Service::class); }
}
