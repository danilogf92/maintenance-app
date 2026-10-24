<?php
namespace App\Modules\Services\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceDocument extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['path'];
    public function service() { return $this->belongsTo(Service::class); }
}
