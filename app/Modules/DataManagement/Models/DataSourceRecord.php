<?php
namespace App\Modules\DataManagement\Models;
use Illuminate\Database\Eloquent\Model;
class DataSourceRecord extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload' => 'encrypted:array']; }
}
