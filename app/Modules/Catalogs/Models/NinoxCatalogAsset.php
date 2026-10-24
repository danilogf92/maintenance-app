<?php
namespace App\Modules\Catalogs\Models;
use Illuminate\Database\Eloquent\Model;
class NinoxCatalogAsset extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['path'];
}
