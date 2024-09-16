<?php

namespace App\Http\Controllers\Api;

use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Http\Traits\CanLoadRelationships;

/**
 * @group Promotions
 *
 * API for managing promotions.
 */
class PromotionController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['services'];

    public function __construct()
    {
        $this->middleware('auth.sanctum')->except(['index', 'show']);
    }

    /**
     * Get a list of Promotions
     *
     * Retrieve a paginated list of promotions. Optionally, include related services by using the `include=services` parameter.
     *
     * @queryParam include string Include related resources, e.g., `services`. Example: ?include=services
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "service_id": 10,
     *       "description": "Discount on spa services",
     *       "valid_until": "2024-12-31",
     *       "created_at": "2024-09-15T12:45:32.000000Z",
     *       "updated_at": "2024-09-15T12:45:32.000000Z",
     *       "service": {
     *         "id": 10,
     *         "name": "Spa Service",
     *         "category_id": 3,
     *         "address": "123 Wellness Street",
     *         "latitude": 34.052235,
     *         "longitude": -118.243683
     *       }
     *     }
     *   ]
     * }
     */
    public function index()
    {
        // gets query builder
        $query = $this->loadRelationships(Promotion::query());

        return PromotionResource::collection(
            $query->paginate()
        );
    }

    /**
     * Create a new Promotion
     *
     * Store a new promotion in the system. You must provide a service ID, description, and optionally the expiration date.
     *
     * @bodyParam service_id integer required The ID of the related service. Example: 10
     * @bodyParam description string required The promotion description. Example: Discount on spa services
     * @bodyParam valid_until date Optional. The expiration date of the promotion in YYYY-MM-DD format. Example: 2024-12-31
     * @response 201 {
     *   "id": 1,
     *   "service_id": 10,
     *   "description": "Discount on spa services",
     *   "valid_until": "2024-12-31",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z"
     * }
     */
    public function store(Request $request)
    {
        $promotion = Promotion::create([
            ...$request->validate([
                'service_id' => 'required|exists:services,id',
                'description' => 'required|string|max:255',
                'valid_until' => 'nullable|date|after_or_equal:today',
            ], $this->validationMessages())
        ]);

        return new PromotionResource($this->loadRelationships($promotion));
    }

   /**
     * Get a specific Promotion
     *
     * Retrieve details of a specific promotion by its ID. Optionally, include the related service by using the `include=services` parameter.
     *
     * @urlParam id integer required The ID of the promotion. Example: 1
     * @queryParam include string Include related resources, e.g., `services`. Example: ?include=services
     * @response 200 {
     *   "id": 1,
     *   "service_id": 10,
     *   "description": "Discount on spa services",
     *   "valid_until": "2024-12-31",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z",
     *   "service": {
     *     "id": 10,
     *     "name": "Spa Service",
     *     "category_id": 3,
     *     "address": "123 Wellness Street",
     *     "latitude": 34.052235,
     *     "longitude": -118.243683
     *   }
     * }
     */
    public function show(Promotion $promotion)
    {
        return new PromotionResource($this->loadRelationships($promotion));
    }

    /**
     * Update a Promotion
     *
     * Update the details of an existing promotion by its ID. You can update the service ID, description, and expiration date.
     *
     * @urlParam id integer required The ID of the promotion. Example: 1
     * @bodyParam service_id integer Optional. The ID of the related service. Example: 10
     * @bodyParam description string Optional. The promotion description. Example: Updated discount on spa services
     * @bodyParam valid_until date Optional. The expiration date of the promotion in YYYY-MM-DD format. Example: 2024-12-31
     * @response 200 {
     *   "id": 1,
     *   "service_id": 10,
     *   "description": "Updated discount on spa services",
     *   "valid_until": "2024-12-31",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T13:00:00.000000Z"
     * }
     */
    public function update(Request $request, Promotion $promotion)
    {
        $promotion->update( 
            $request->validate([
                'service_id' => 'sometimes|exists:services,id',
                'description' => 'sometimes|string|max:255',
                'valid_until' => 'nullable|date|after_or_equal:today',
            ], $this->validationMessages())
        );

        return new PromotionResource($this->loadRelationships($promotion));
    }

    /**
     * Delete a Promotion
     *
     * Delete a specific promotion by its ID. This action is irreversible.
     *
     * @urlParam id integer required The ID of the promotion. Example: 1
     * @response 204 {
     *   "message": "Promotion deleted successfully"
     * }
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return response(status: 204);    
    }

     /**
     * Custom error messages for validation.
     */
    protected function validationMessages()
    {
        return [
            'service_id.required' => 'The service ID is required.',
            'service_id.exists' => 'The selected service does not exist.',

            'description.required' => 'The description field is required.',
            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description may not be greater than 255 characters.',

            'valid_until.date' => 'The valid until field must be a valid date.',
            'valid_until.after_or_equal' => 'The valid until date must be today or a future date.',
        ];
    }
}

