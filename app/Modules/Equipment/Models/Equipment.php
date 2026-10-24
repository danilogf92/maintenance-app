<?php

namespace App\Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $guarded = ['id'];

    public function receivedSource() { return $this->hasOne(ReceivedEquipment::class); }

    public function brandRecord() { return $this->belongsTo(\App\Modules\Brands\Models\Brand::class, 'brand_id'); }
    public function modelRecord() { return $this->belongsTo(\App\Modules\EquipmentModels\Models\EquipmentModel::class, 'equipment_model_id'); }

    public function client()
    {
        return $this->belongsTo(\App\Modules\Clients\Models\Client::class, 'client_id');
    }

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'institution_id');
    }

    public function tickets()
    {
        return $this->hasMany(\App\Modules\Tickets\Models\Ticket::class, 'equipment_id');
    }

}
