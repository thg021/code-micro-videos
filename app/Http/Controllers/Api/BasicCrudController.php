<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();
    protected abstract function resource();
    
    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id){
        $model = $this->model();
        $keyname = (new $model)->getRouteKeyName();
        return $this->model()::where($keyname, $id)->firstOrFail();
    }


    public function show($id)
    {
        $obj = $this->findOrFail($id);
        return $obj;
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
