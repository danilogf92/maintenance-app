<?php
namespace App\Modules\DataManagement\Models;
use App\Modules\DataManagement\Configuration\EquipmentHistories;
use Illuminate\Database\Eloquent\Model;
class EquipmentHistory extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload' => 'encrypted:array', 'issues' => 'array', 'date_only' => 'boolean']; }
    public static function forEntity(string $table): self
    {
        if (!isset(EquipmentHistories::all()[$table])) { throw new \InvalidArgumentException('Entidad de historial inválida.'); }
        $model = (new self)->setTable($table);
        return $model->fillable(['source_id', 'source_hash', 'payload', 'equipment_id', EquipmentHistories::all()[$table]['column'], 'assigned_at', 'date_only', 'issues']);
    }
}
