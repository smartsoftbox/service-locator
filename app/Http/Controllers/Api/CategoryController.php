<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\CanLoadRelationships;

/**
 * @group Categories
 *
 * API for managing categories.
 */
class CategoryController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['services'];

    public function __construct()
    {
        $this->middleware('auth.sanctum')->except(['index', 'show']);
    }

    /**
     * List Categories
     *
     * Returns a list of categories.
     * 
     * @response 200 {
     *  "data": [
     *      {
     *          "id": 1,
     *          "name": "Electronics",
     *          "description": "This is category description",
     *          "created_at": "2023-09-14 19:33:33",
     *          "updated_at": "2023-09-14 19:33:33"
     *      }
     *  ]
     * }
     */
    public function index()
    {
        // gets query builder
        $query = $this->loadRelationships(Category::query());

        return CategoryResource::collection(
            $query->paginate()
        );
    }

    /**
     * Create a new Category
     *
     * @bodyParam name string required The name of the category. Example: "Clothing"
     * @bodyParam description string Optional. The description of the category. Example: Category for restaurants
     * @response 201 {
     *  "id": 2,
     *  "name": "Clothing",
     *  "description": "This is category description",
     *  "created_at": "2023-09-14 19:33:33",
     *  "updated_at": "2023-09-14 19:33:33"
     * }
     */
    public function store(Request $request)
    {
        $category = Category::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
            ])
        ]);

        return new CategoryResource($this->loadRelationships($category));
    }

    /**
     * Get a specific Category
     *
     * Retrieve details of a specific category by its ID.
     *
     * @urlParam id integer required The ID of the category. Example: 1
     * @response 200 {
     *   "id": 1,
     *   "name": "Hotels",
     *   "description": "Category for hotels and accommodations",
     *   "created_at": "2024-09-14T19:33:33.000000Z",
     *   "updated_at": "2024-09-14T19:33:33.000000Z"
     * }
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update a specific Category
     *
     * Update the details of a category by its ID. Only fields provided in the request will be updated.
     *
     * @urlParam id integer required The ID of the category. Example: 1
     * @bodyParam name string Optional. The name of the category. Example: Restaurants
     * @bodyParam description string Optional. The description of the category. Example: Category for restaurants
     * @response 200 {
     *   "id": 1,
     *   "name": "Restaurants",
     *   "description": "Category for restaurants",
     *   "created_at": "2024-09-14T19:33:33.000000Z",
     *   "updated_at": "2024-09-14T19:45:45.000000Z"
     * }
     */
    public function update(Request $request, Category $category)
    {
        $category->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ])
        );

        return new CategoryResource($this->loadRelationships($category));
    }

    /**
     * Delete a specific Category
     *
     * Delete the category by its ID. This action is irreversible.
     *
     * @urlParam id integer required The ID of the category. Example: 1
     * @response 204 {
     *   "message": "Category deleted successfully"
     * }
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response(status: 204);
    }
}
