<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Service;
use Illuminate\Http\Request;

/**
 * @group Services
 *
 * API for managing services.
 */
class ServiceController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['category', 'reviews', 'reviews.user'];

    public function __construct()
    {
        $this->middleware('auth.sanctum')->except(['index', 'show']);
    }

    /**
     * Get a list of Services
     *
     * Retrieve a paginated list of services. Optionally, include related categories, reviews, or review authors (users) by using the `include` parameter.
     *
     * @queryParam include string Include related resources. Possible values: `category`, `reviews`, `reviews.user`. Example: ?include=category,reviews,reviews.user
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Spa Service",
     *       "category_id": 3,
     *       "address": "123 Wellness Street",
     *       "latitude": 34.052235,
     *       "longitude": -118.243683,
     *       "rating": 4.5,
     *       "review_count": 10,
     *       "opening_hours": {
     *         "Monday": "08:00-20:00",
     *         "Tuesday": "08:00-20:00"
     *       },
     *       "created_at": "2024-09-15T12:45:32.000000Z",
     *       "updated_at": "2024-09-15T12:45:32.000000Z",
     *       "category": {
     *         "id": 3,
     *         "name": "Wellness"
     *       },
     *       "reviews": [
     *         {
     *           "id": 1,
     *           "rating": 5,
     *           "comment": "Excellent service!",
     *           "user": {
     *             "id": 5,
     *             "name": "John Doe"
     *           }
     *         }
     *       ]
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/services?page=1",
     *     "last": "http://example.com/api/services?page=3",
     *     "prev": null,
     *     "next": "http://example.com/api/services?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 3,
     *     "path": "http://example.com/api/services",
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 45
     *   }
     * }
     */
    public function index()
    {
        // gets query builder
        $query = $this->loadRelationships(Service::query());

        return ServiceResource::collection(
            $query->paginate()
        );
    }

    /**
     * Create a new Service
     *
     * Store a new service. You must provide a name, category ID, address, latitude, longitude, and optionally other details.
     *
     * @bodyParam name string required The name of the service. Example: Spa Service
     * @bodyParam category_id integer required The ID of the category this service belongs to. Example: 3
     * @bodyParam address string required The address of the service. Example: 123 Wellness Street
     * @bodyParam latitude decimal required The latitude coordinate. Example: 34.052235
     * @bodyParam longitude decimal required The longitude coordinate. Example: -118.243683
     * @bodyParam rating decimal optional The rating of the service, between 0 and 5. Example: 4.5
     * @bodyParam review_count integer optional The number of reviews. Example: 10
     * @bodyParam opening_hours json optional The opening hours of the service. Example: {"Monday": "08:00-20:00", "Tuesday": "08:00-20:00"}
     * @response 201 {
     *   "id": 1,
     *   "name": "Spa Service",
     *   "category_id": 3,
     *   "address": "123 Wellness Street",
     *   "latitude": 34.052235,
     *   "longitude": -118.243683,
     *   "rating": 4.5,
     *   "review_count": 10,
     *   "opening_hours": {
     *     "Monday": "08:00-20:00",
     *     "Tuesday": "08:00-20:00"
     *   },
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z"
     * }
    **/
    public function store(Request $request)
    {
        $service = Service::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'address' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'opening_hours' => 'nullable|json',
            ], $this->validationMessages())
        ]);

        return new ServiceResource($this->loadRelationships($service));
    }

    /**
     * Get a specific Service
     *
     * Retrieve details of a specific service by its ID. Optionally, include related category, reviews, or review authors by using the `include` parameter.
     *
     * @urlParam id integer required The ID of the service. Example: 1
     * @queryParam include string Include related resources. Possible values: `category`, `reviews`, `reviews.user`. Example: ?include=category,reviews,reviews.user
     * @response 200 {
     *   "id": 1,
     *   "name": "Spa Service",
     *   "category_id": 3,
     *   "address": "123 Wellness Street",
     *   "latitude": 34.052235,
     *   "longitude": -118.243683,
     *   "rating": 4.5,
     *   "review_count": 10,
     *   "opening_hours": {
     *     "Monday": "08:00-20:00",
     *     "Tuesday": "08:00-20:00"
     *   },
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z",
     *   "category": {
     *     "id": 3,
     *     "name": "Wellness"
     *   },
     *   "reviews": [
     *     {
     *       "id": 1,
     *       "rating": 5,
     *       "comment": "Excellent service!",
     *       "user": {
     *         "id": 5,
     *         "name": "John Doe"
     *       }
     *     }
     *   ]
     * }
     */
    public function show(Service $service)
    {
        return new ServiceResource($this->loadRelationships($service));
    }

    /**
     * Update a Service
     *
     * Update the details of an existing service by its ID. You can update the name, address, or other service details.
     *
     * @urlParam id integer required The ID of the service. Example: 1
     * @bodyParam name string optional The name of the service. Example: Updated Spa Service
     * @bodyParam category_id integer optional The ID of the category this service belongs to. Example: 4
     * @bodyParam address string optional The address of the service. Example: 456 Wellness Avenue
     * @bodyParam latitude decimal optional The latitude coordinate. Example: 34.052235
     * @bodyParam longitude decimal optional The longitude coordinate. Example: -118.243683
     * @bodyParam rating decimal optional The rating of the service, between 0 and 5. Example: 4.7
     * @bodyParam review_count integer optional The number of reviews. Example: 12
     * @bodyParam opening_hours json optional The opening hours of the service. Example: {"Monday": "09:00-21:00", "Tuesday": "09:00-21:00"}
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Spa Service",
     *   "category_id": 4,
     *   "address": "456 Wellness Avenue",
     *   "latitude": 34.052235,
     *   "longitude": -118.243683,
     *   "rating": 4.7,
     *   "review_count": 12,
     *   "opening_hours": {
     *     "Monday": "09:00-21:00",
     *     "Tuesday": "09:00-21:00"
     *   },
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-16T12:45:32.000000Z"
     * }
     * @response 404 {
     *   "message": "Service not found"
     * }
     */
    public function update(Request $request, Service $service)
    {
        $service->update( 
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'category_id' => 'sometimes|exists:categories,id',
                'address' => 'sometimes|string|max:255',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'opening_hours' => 'nullable|json',
            ], $this->validationMessages())
        );

        return new ServiceResource($this->loadRelationships($service));
    }

    /**
     * Delete a Service
     *
     * Delete a specific service by its ID.
     *
     * @urlParam id integer required The ID of the service. Example: 1
     * @response 204 
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return response(status: 204);    
    }

    /**
     * Delete a Service
     *
     * Delete a specific service by its ID.
     *
     * @urlParam id integer required The ID of the service. Example: 1
     * @response 204 {
     *   "description": "No content. The resource was successfully deleted."
     * }
     * @response 404 {
     *   "description": "The resource was not found."
     * }
     */
    protected function validationMessages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            
            'category_id.required' => 'The category field is required.',
            'category_id.exists' => 'The selected category does not exist.',

            'address.required' => 'The address field is required.',
            'address.string' => 'The address must be a valid string.',
            'address.max' => 'The address may not be greater than 255 characters.',

            'latitude.required' => 'The latitude is required.',
            'latitude.numeric' => 'The latitude must be a valid number.',
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',

            'longitude.required' => 'The longitude is required.',
            'longitude.numeric' => 'The longitude must be a valid number.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',

            'rating.numeric' => 'The rating must be a valid number.',
            'rating.min' => 'The rating must be at least 0.',
            'rating.max' => 'The rating may not be greater than 5.',

            'review_count.integer' => 'The review count must be a valid integer.',
            'review_count.min' => 'The review count cannot be less than 0.',

            'opening_hours.json' => 'The opening hours field must be a valid JSON structure.',
        ];
    }
}
