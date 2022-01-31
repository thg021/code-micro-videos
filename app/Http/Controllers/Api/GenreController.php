<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;

        $obj = \DB::transaction(
            function()
            use($request, $self, $validatedData)
            {
                /** @var Video $obj */
                $obj = $this->model()::create($validatedData);
                $self->handleRelations($obj, $request);
                return $obj;
            }
        );
        $obj->refresh(); 
        return $obj;
    }

    public function update(Request $request, $id)
    {     
       $obj = $this->findOrFail($id);
       $self = $this; 
       $validatedData = $this->validate($request, $this->rulesUpdate());
       $obj = \DB::transaction(
         function() use($request, $validatedData, $self, $obj) {
         /** @var Video $obj */
         $obj->update($validatedData);
         $self->handleRelations($obj, $request); 
         return $obj;
      });
       return  $obj;
    }

    protected function handleRelations($genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genre::class;
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
