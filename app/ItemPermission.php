<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPermission extends Model
{
    protected $fillable = [
        'type', 'name', 'model_name', 'model_id'
    ];
}
