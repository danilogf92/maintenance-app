<?php
namespace App\Modules\Services\Models;
use Illuminate\Database\Eloquent\Model;
class NinoxServiceImport extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload' => 'encrypted:array']; }
    public function service() { return $this->belongsTo(Service::class); }
}
