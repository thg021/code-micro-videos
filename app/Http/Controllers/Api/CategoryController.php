<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    private $rules = [
        'name' => 'required|max:255', 
        'is_active' => 'boolean'
    ];
    
    public function index(Request $request)
    {
        //dump($request);
        if($request->has('only_trashed'))
        {
            return Category::onlyTrashed()->get();
        }
        //Get 
        
        return Category::all();
    }


    public function store(Request $request)
    {
        //Post
        //Regras usando o validate que esta incluso no Controller
        $this->validate($request, $this->rules);
        $category = Category::create($request->all());
        $category->refresh();
        return $category;
    }

    public function show(Category $category)
    {
        //Pesquisa por params
        //dump($category->getAttribute('id'));
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        //Put e Patch
        $this->validate($request, $this->rules);
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category)
    {
        //delete
        
        $category->delete();
        return response()->noContent();//204
    }
}
