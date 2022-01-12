<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    //quais campos sao seguros
    use SoftDeletes, Traits\Uuid;
    protected $fillable = ['name', 'description', 'is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public $incrementing = false;
    protected $keyType = 'string';
}
