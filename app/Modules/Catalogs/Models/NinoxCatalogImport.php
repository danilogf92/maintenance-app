<?php
namespace App\Modules\Catalogs\Models;
use Illuminate\Database\Eloquent\Model;
class NinoxCatalogImport extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload' => 'encrypted:array']; }
    public function assets() { return $this->hasMany(NinoxCatalogAsset::class); }
}
