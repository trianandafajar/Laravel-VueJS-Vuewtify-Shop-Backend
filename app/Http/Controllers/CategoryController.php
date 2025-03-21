<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Categories as CategoryResourceCollection;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Paginate the categories with a default per-page value of 6.
        $criteria = Category::paginate(6);
        return new CategoryResourceCollection($criteria);
    }

    /**
     * Display a random list of categories.
     *
     * @param  int  $count
     * @return \Illuminate\Http\Response
     */
    public function random(int $count)
    {
        // Get random categories based on the count specified.
        $criteria = Category::inRandomOrder()->limit($count)->get();
        return new CategoryResourceCollection($criteria);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Optionally, implement store logic for category creation
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Return category with the given ID or a 404 response if not found
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return new CategoryResource($category);
    }

    /**
     * Display the category by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function slug($slug)
    {
        // Find category by slug
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Optionally, implement update logic for category
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Optionally, implement destroy logic for category
    }
}
