<?php

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function equipment()
    {
        return $this->hasMany(\App\Modules\Equipment\Models\Equipment::class, 'client_id');
    }

}
