<?php
namespace App\Modules\EquipmentModels\Models;

class EquipmentModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'equipment_models';
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'lock_version' => 'integer']; }
    public function ninoxImport() { return $this->hasOne(\App\Modules\Catalogs\Models\NinoxCatalogImport::class, 'record_id')->where('catalog', 'equipment_models'); }
    public function brand() { return $this->belongsTo(\App\Modules\Brands\Models\Brand::class); }
    public function equipment() { return $this->hasMany(\App\Modules\Equipment\Models\Equipment::class); }
}
