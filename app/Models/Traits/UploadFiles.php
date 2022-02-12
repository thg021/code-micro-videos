<?php 

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;


trait UploadFiles 
{

    public $oldFiles = [];

    protected abstract function uploadDir();

    public static function bootUploadFiles()
    {

        static::updating(function (Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            });
            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $filesFiltered);
        });

        // //metodo será executando antes do update 
        // static::updating(function (Model $model){
        //     $fieldsUpdated = array_keys($model->getDirty());
        //     $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
        //     //Retirando os null
        //     $filesFiltered = Arr::where($filesUpdated, function($fileField) use($model) {
        //         return $model->getOriginal($fileField);
        //     });
        //     //dd($model->oldFiles);
        //     //Retornando o valor dos campos
        //     $model->oldFiles = array_map(function($fileField) use($model) {
        //         return $model->getOriginal($fileField);
        //     }, $filesFiltered);
        // });
    }

    /**
     * Undocumented function
     *
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach($files as $file){
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
    }

    public function deleteFiles(array $files)
    {
        foreach($files as $file){
            $this->deleteFile($file);
        }
    }

    /**
     * Undocumented function
     *
     * @param string|UploadFile $file
     * @return void
     */    
    public function deleteFile($file)
    {   
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        \Storage::delete("{$this->uploadDir()}/{$filename}");
    }

    public static function extractFiles(array &$attributes = [])
    {
        $files = [];
        foreach (self::$fileFields as $file) {
            if(isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile ){
                $files[] = $attributes[$file]; 
                $attributes[$file] = $attributes[$file]->hashName(); 
            }
        }

        return $files;
    }
}
