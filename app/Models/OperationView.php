<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationView extends Model
{
    protected $fillable = ['user_id', 'module', 'name', 'filter_value', 'columns', 'table_settings', 'is_default', 'sort_order'];

    protected $casts = ['columns' => 'array', 'table_settings' => 'array', 'is_default' => 'boolean'];
}
