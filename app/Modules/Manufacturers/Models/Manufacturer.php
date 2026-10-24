<?php
namespace App\Modules\Manufacturers\Models;

class Manufacturer extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'manufacturers';
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'lock_version' => 'integer']; }
    public function brands() { return $this->hasMany(\App\Modules\Brands\Models\Brand::class); }
}
