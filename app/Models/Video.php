<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Composer\Command\StatusCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use phpDocumentor\Reflection\Types\Static_;

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
    protected $dates = [
        'deleted_at'
    ];
    protected $casts = [
        'opened' => 'boolean',
        'year_launched' => 'integer', 
        'duration' => 'integer',
    ]; 

    public $incrementing = false;
    protected $keyType = 'string';

    public static function create(array $attributes = [])
    {
        try {
            \DB::beginTransaction();
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            \DB::commit();
            return $obj;
        } catch (\Exception $e) {
            //throw $th;
            if(isset($obj)){
                //excluir os arquivos de uploads feitos 
            }
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if($saved){
                //fazer upload dos novos arquivos
                //excluir os antigos

            }
            \DB::commit();
            return $saved;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }

    }

    public static function handleRelations(Video $videos, array $attributes)
    {
        if(isset($attributes['categories_id'])){
            $videos->categories()->sync($attributes['categories_id']);
        }

        if(isset($attributes['genres_id'])){
            $videos->genres()->sync($attributes['genres_id']);
        }
    
    }
    
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }
}
