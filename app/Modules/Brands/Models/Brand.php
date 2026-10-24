<?php
namespace App\Modules\Brands\Models;

class Brand extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'brands';
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'lock_version' => 'integer']; }
    public function ninoxImport() { return $this->hasOne(\App\Modules\Catalogs\Models\NinoxCatalogImport::class, 'record_id')->where('catalog', 'brands'); }
    public function manufacturer() { return $this->belongsTo(\App\Modules\Manufacturers\Models\Manufacturer::class); }
    public function models() { return $this->hasMany(\App\Modules\EquipmentModels\Models\EquipmentModel::class); }
    public function equipment() { return $this->hasMany(\App\Modules\Equipment\Models\Equipment::class); }
}
