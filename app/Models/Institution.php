<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = ['name', 'code', 'type'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
