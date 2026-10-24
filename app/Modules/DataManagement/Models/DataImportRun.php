<?php
namespace App\Modules\DataManagement\Models;
use Illuminate\Database\Eloquent\Model;
class DataImportRun extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected function casts(): array { return ['summary' => 'array', 'issues' => 'array', 'link_results' => 'array', 'finished_at' => 'datetime']; }
}
