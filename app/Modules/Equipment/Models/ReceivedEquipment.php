<?php
namespace App\Modules\Equipment\Models;
use Illuminate\Database\Eloquent\Model;
class ReceivedEquipment extends Model
{
    protected $table = 'received_equipment';
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload'=>'encrypted:array']; }
    public function equipment() { return $this->belongsTo(Equipment::class); }
}
