<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();

    // private $rules = [
    //     'name' => 'required|max:255', 
    //     'is_active' => 'boolean'
    // ];
    
    public function index()
    {
      
        // if($request->has('only_trashed'))
        // {
        //     return $this->model()::onlyTrashed()->get();
        // }
       
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        return $obj;
    }

    protected function findOrFail($id){
        $model = $this->model();
        $keyname = (new $model)->getRouteKeyName();
        return $this->model()::where($keyname, $id)->firstOrFail();
    }


    // public function show(Category $category)
    // {
    //     //Pesquisa por params
    //     //dump($category->getAttribute('id'));
    //     return $category;
    // }

    // public function update(Request $request, Category $category)
    // {
    //     //Put e Patch
    //     $this->validate($request, $this->rules);
    //     $category->update($request->all());
    //     return $category;
    // }

    // public function destroy(Category $category)
    // {
    //     //delete
        
    //     $category->delete();
    //     return response()->noContent();//204
    // }
}
