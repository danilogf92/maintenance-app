<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationViewPreference extends Model
{
    protected $fillable = ['user_id', 'module', 'operation_view_id'];
}
