<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();

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


    public function show($id)
    {
        $category = $this->findOrFail($id);
        return $category;
    }

    public function update(Request $request, $id)
    {
       
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validatedData);
        return  $obj;
    }

    public function destroy($id)
    {
        //delete
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();//204
    }
}
