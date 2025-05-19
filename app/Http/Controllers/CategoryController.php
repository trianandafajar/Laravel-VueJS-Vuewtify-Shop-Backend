<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Categories as CategoryResourceCollection;

class CategoryController extends Controller
{
    /**
     * Get all categories with pagination.
     */
    public function index()
    {
        $categories = Category::select('id', 'name', 'slug')->paginate(6);
        return new CategoryResourceCollection($categories);
    }

    /**
     * Get random categories.
     */
    public function random(int $count)
    {
        $categories = Category::select('id', 'name', 'slug')
            ->inRandomOrder()
            ->limit($count)
            ->get();

        return new CategoryResourceCollection($categories);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Category added successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Get category by ID.
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return new CategoryResource($category);
    }

    /**
     * Get category by slug.
     */
    public function slug($slug)
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return new CategoryResource($category);
    }

    /**
     * Update category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id,
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $id,
        ]);

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ], 200);
    }

    /**
     * Delete a category.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ], 200);
    }
}