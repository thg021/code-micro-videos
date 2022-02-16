<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BasicCrudController;
use App\Rules\GenresHasCategoriesRule;

class VideoController extends BasicCrudController
{

   private $rules;

   public function __construct()
   {
      $this->rules = [
         'title' => 'required|max:255', 
         'description' => 'required', 
         'year_launched' => 'required|date_format:Y', 
         'opened' => 'boolean', 
         'rating' => 'required|in:' . implode(',', Video::RATING_LIST), 
         'duration' => 'required|integer', 
         'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL', 
         'genres_id' => [
            'required', 
            'array', 
            'exists:genres,id,deleted_at,NULL'
         ], 
         'video_file' => 'mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
         'thumb_file' => 'mimetypes:image/jpeg|max:' . Video::THUMB_FILE_MAX_SIZE, 
         'banner_file' => 'mimetypes:image/jpeg|max:' . Video::BANNER_FILE_MAX_SIZE,
         'trailer_file' => 'mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE,  
      ];
   }

   public function store(Request $request)
   {
      $this->addRuleIfGenreHasCategories($request);
      $validatedData = $this->validate($request, $this->rulesStore());
      /** @var Video $obj */
      $obj = $this->model()::create($validatedData);
      $obj->refresh();
      return $obj;

      //$self = $this; 
      // $obj = \DB::transaction(
      //    function() use($request, $validatedData, $self) {
      //    /** @var Video $obj */
      //    $obj = $this->model()::create($validatedData);
      //    $self->handleRelations($obj, $request); 
      //    return $obj;
      // });
           
   }

   public function update(Request $request, $id)
   {     
      $obj = $this->findOrFail($id);
      $self = $this; 
      $this->addRuleIfGenreHasCategories($request);
      $validatedData = $this->validate($request, $this->rulesUpdate());
      /** @var Video $obj */
      $obj->update($validatedData);
      return  $obj;
   }
   

   protected function addRuleIfGenreHasCategories(Request $request)
   {
      $categoriesId = $request->get('categories_id');
      $categoriesId = is_array($categoriesId) ? $categoriesId : [];
      $this->rules['genres_id'][]= new GenresHasCategoriesRule(
            $categoriesId
         );
   }

   protected function model()
   {
      return Video::class;
   }

   protected function rulesStore()
   {
       return $this->rules;
   }

   protected function rulesUpdate()
   {
       return $this->rules;
   }
}
