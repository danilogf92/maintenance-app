<?php

namespace App\Modules\Access\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GoogleAccount extends Model
{
    protected $fillable = ['user_id', 'subject', 'email'];
    protected $hidden = ['subject'];

    public function user() { return $this->belongsTo(User::class); }
}
