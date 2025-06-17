<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CategoryRepository;
use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Categories as CategoryResourceCollection;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories
     */
    public function index(): CategoryResourceCollection
    {
        $categories = $this->categoryRepository->getAll();
        return new CategoryResourceCollection($categories);
    }

    /**
     * Get random categories
     */
    public function random(int $count): CategoryResourceCollection
    {
        $categories = $this->categoryRepository->getRandom($count);
        return new CategoryResourceCollection($categories);
    }

    /**
     * Get category by slug
     */
    public function slug(string $slug): JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($slug);
        
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Store a new category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string'
        ]);

        $category = $this->categoryRepository->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($slug);
        
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $this->categoryRepository->update($category, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(string $slug): JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($slug);
        
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $this->categoryRepository->delete($category);

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }
}