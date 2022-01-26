<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title', 
        'description', 
        'year_launched', 
        'opened', 
        'rating', 
        'duration'
    ]; 
    protected $casts = [
        'year_launched' => 'integer', 
        'opened' => 'boolean', 
        'duration' => 'integer'
    ]; 
    protected $dates = [
        'deleted_at'
    ];
    public $incremented = false;
    protected $keyType = 'string';
}