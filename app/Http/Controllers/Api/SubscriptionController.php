<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Enums\SubscriptionPlan;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Traits\CanLoadRelationships;
use App\Http\Resources\SubscriptionResource;

/**
 * @group Subscriptions
 *
 * API for managing subscriptions.
 */
class SubscriptionController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['services'];
    
    public function __construct()
    {
        $this->middleware('auth.sanctum')->except(['index', 'show']);
    }

    /**
     * List Subscriptions
     *
     * Retrieve a list of subscriptions with optional included relations.
     *
     * @queryParam include string optional Comma-separated list of relations to include. Possible values: services. Example: services
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "service_id": 1,
     *       "plan": "premium",
     *       "started_at": "2024-09-01T00:00:00Z",
     *       "expires_at": "2025-09-01T00:00:00Z",
     *       "service": {
     *         "id": 1,
     *         "name": "Sample Service",
     *         "address": "123 Service St",
     *         "latitude": 37.774929,
     *         "longitude": -122.419418
     *       }
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/subscriptions?page=1",
     *     "last": "http://example.com/api/subscriptions?page=10",
     *     "prev": null,
     *     "next": "http://example.com/api/subscriptions?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 10,
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 150
     *   }
     * }
     */
    public function index()
    {
        // gets query builder
        $query = $this->loadRelationships(Subscription::query());

        return SubscriptionResource::collection(
            $query->paginate()
        );
    }

    /**
     * Create a Subscription
     *
     * Store a new subscription in the database.
     *
     * @bodyParam service_id integer required The ID of the service to subscribe to. Example: 1
     * @bodyParam plan string required The type of subscription plan. Example: premium
     * @bodyParam started_at timestamp required The start date of the subscription. Example: 2024-09-01T00:00:00Z
     * @bodyParam expires_at timestamp optional The end date of the subscription. Example: 2025-09-01T00:00:00Z
     * @response 201 {
     *   "id": 1,
     *   "service_id": 1,
     *   "plan": "premium",
     *   "started_at": "2024-09-01T00:00:00Z",
     *   "expires_at": "2025-09-01T00:00:00Z",
     *   "service": {
     *     "id": 1,
     *     "name": "Sample Service",
     *     "address": "123 Service St",
     *     "latitude": 37.774929,
     *     "longitude": -122.419418
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "service_id": ["The service id field is required."],
     *     "plan": ["The plan field is required."],
     *     "started_at": ["The started at field is required."]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $subscription = Subscription::create([
            ...$request->validate([
                'service_id' => 'required|exists:services,id',
                'plan' => ['required', Rule::in(array_column(SubscriptionPlan::cases(), 'value'))],
                'started_at' => 'required|date',
                'expires_at' => 'nullable|date|after:started_at',
            ], $this->validationMessages())
        ]);

        return new SubscriptionResource($this->loadRelationships($subscription));
    }

    /**
     * Get a Subscription
     *
     * Retrieve a specific subscription by its ID.
     *
     * @urlParam id integer required The ID of the subscription. Example: 1
     * @queryParam include string optional Comma-separated list of relations to include. Possible values: services. Example: services
     * @response 200 {
     *   "id": 1,
     *   "service_id": 1,
     *   "plan": "premium",
     *   "started_at": "2024-09-01T00:00:00Z",
     *   "expires_at": "2025-09-01T00:00:00Z",
     *   "service": {
     *     "id": 1,
     *     "name": "Sample Service",
     *     "address": "123 Service St",
     *     "latitude": 37.774929,
     *     "longitude": -122.419418
     *   }
     * }
     * @response 404 {
     *   "message": "Subscription not found"
     * }
     */
    public function show(Subscription $subscription)
    {
        return new SubscriptionResource($this->loadRelationships($subscription));
    }

    /**
     * Update a Subscription
     *
     * Update the details of an existing subscription by its ID.
     *
     * @urlParam id integer required The ID of the subscription. Example: 1
     * @bodyParam service_id integer optional The ID of the service to update the subscription for. Example: 2
     * @bodyParam plan string optional The type of subscription plan. Example: free
     * @bodyParam started_at timestamp optional The start date of the subscription. Example: 2024-09-01T00:00:00Z
     * @bodyParam expires_at timestamp optional The end date of the subscription. Example: 2025-09-01T00:00:00Z
     * @response 200 {
     *   "id": 1,
     *   "service_id": 2,
     *   "plan": "free",
     *   "started_at": "2024-09-01T00:00:00Z",
     *   "expires_at": "2025-09-01T00:00:00Z",
     *   "service": {
     *     "id": 2,
     *     "name": "Updated Service",
     *     "address": "456 Service Ave",
     *     "latitude": 37.774929,
     *     "longitude": -122.419418
     *   }
     * }
     * @response 404 {
     *   "message": "Subscription not found"
     * }
     */
    public function update(Request $request, Subscription $subscription)
    {
        $subscription->update( 
            $request->validate([
                'service_id' => 'sometimes|exists:services,id',
                'plan' => ['required', Rule::in(array_column(SubscriptionPlan::cases(), 'value'))],
                'started_at' => 'sometimes|date',
                'expires_at' => 'nullable|date|after:started_at',
            ], $this->validationMessages())
        );

        return new SubscriptionResource($this->loadRelationships($subscription));
    }

    /**
     * Delete a Subscription
     *
     * Delete a specific subscription by its ID.
     *
     * @urlParam id integer required The ID of the subscription. Example: 1
     * @response 204 {
     *   "description": "No content. The resource was successfully deleted."
     * }
     * @response 404 {
     *   "description": "The resource was not found."
     * }
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

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

            'plan.required' => 'The subscription plan is required.',
            'plan.in' => 'The subscription plan must be either "free" or "premium".',

            'started_at.required' => 'The start date of the subscription is required.',
            'started_at.date' => 'The start date must be a valid date.',

            'expires_at.date' => 'The expiration date must be a valid date.',
            'expires_at.after' => 'The expiration date must be after the start date.',
        ];
    }
}
