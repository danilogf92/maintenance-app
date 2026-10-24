<?php

namespace App\Modules\Contracts\Models;

use App\Modules\Clients\Models\Client;
use Illuminate\Database\Eloquent\Model;

class MaintenanceContract extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'lock_version' => 'integer'];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function coverages()
    {
        return $this->hasMany(EquipmentCoverage::class, 'maintenance_contract_id');
    }
}
