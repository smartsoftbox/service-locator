<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\CanLoadRelationships;
use PhpParser\Lexer\TokenEmulator\ReverseEmulator;

/**
 * @group Reviews
 *
 * API for managing reviews.
 */
class ReviewController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['service', 'reviews.user'];


    public function __construct()
    {
        $this->middleware('auth.sanctum')->except(['index', 'show']);
    }

    /**
     * Get a list of Reviews
     *
     * Retrieve a paginated list of reviews. Optionally, include related services or review authors (users) by using the `include` parameter.
     *
     * @queryParam include string Include related resources. Possible values: `service`, `reviews.user`. Example: ?include=service,reviews.user
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "service_id": 10,
     *       "user_id": 5,
     *       "rating": 5,
     *       "comment": "Excellent service!",
     *       "created_at": "2024-09-15T12:45:32.000000Z",
     *       "updated_at": "2024-09-15T12:45:32.000000Z",
     *       "service": {
     *         "id": 10,
     *         "name": "Spa Service",
     *         "category_id": 3,
     *         "address": "123 Wellness Street",
     *         "latitude": 34.052235,
     *         "longitude": -118.243683
     *       },
     *       "user": {
     *         "id": 5,
     *         "name": "John Doe"
     *       }
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/reviews?page=1",
     *     "last": "http://example.com/api/reviews?page=3",
     *     "prev": null,
     *     "next": "http://example.com/api/reviews?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 3,
     *     "path": "http://example.com/api/reviews",
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 45
     *   }
     * }
     */
    public function index(Service $service)
    {
        $reviews = $this->loadRelationships($service)->latest();

        return ReviewResource::collection(
            $reviews->paginate()
        );
    }

    /**
     * Create a new Review
     *
     * Store a new review for a specific service. You must provide a service ID, user ID, rating, and optionally a comment.
     *
     * @bodyParam service_id integer required The ID of the related service. Example: 10
     * @bodyParam user_id integer required The ID of the user posting the review. Example: 5
     * @bodyParam rating integer required The rating for the service, between 1 and 5. Example: 5
     * @bodyParam comment string optional The comment for the review. Example: Excellent service!
     * @response 201 {
     *   "id": 1,
     *   "service_id": 10,
     *   "user_id": 5,
     *   "rating": 5,
     *   "comment": "Excellent service!",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z"
     * }
     */
    public function store(Request $request, Service $service)
    {
        $review = $service->reviews()->create([
            ...$request->validate([
                    'service_id' => 'required|exists:services,id',
                    'rating' => 'required|integer|min:1|max:5',
                    'comment' => 'nullable|string|max:1000',
                ], $this->validationMessages()),
            'user_id' => $request->user()->id,
        ]);

        return new ReviewResource($this->loadRelationships($review));
    }

    /**
     * Create a new Review
     *
     * Store a new review for a specific service. You must provide a service ID, user ID, rating, and optionally a comment.
     *
     * @bodyParam service_id integer required The ID of the related service. Example: 10
     * @bodyParam user_id integer required The ID of the user posting the review. Example: 5
     * @bodyParam rating integer required The rating for the service, between 1 and 5. Example: 5
     * @bodyParam comment string optional The comment for the review. Example: Excellent service!
     * @response 201 {
     *   "id": 1,
     *   "service_id": 10,
     *   "user_id": 5,
     *   "rating": 5,
     *   "comment": "Excellent service!",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T12:45:32.000000Z"
     * }
     */
    public function show(Service $service, Review $review)
    {
        return new ReviewResource($this->loadRelationships($review));
    }

    /**
     * Update a Review
     *
     * Update the details of an existing review by its ID. You can update the rating, comment, or related service.
     *
     * @urlParam id integer required The ID of the review. Example: 1
     * @bodyParam rating integer optional The rating for the service, between 1 and 5. Example: 4
     * @bodyParam comment string optional The comment for the review. Example: Good service, but room for improvement.
     * @bodyParam service_id integer optional The ID of the related service. Example: 11
     * @response 200 {
     *   "id": 1,
     *   "service_id": 11,
     *   "user_id": 5,
     *   "rating": 4,
     *   "comment": "Good service, but room for improvement.",
     *   "created_at": "2024-09-15T12:45:32.000000Z",
     *   "updated_at": "2024-09-15T13:00:00.000000Z"
     * }
     */
    public function update(Request $request, Review $review)
    {
        // if(Gate::denies('update-review', $review)) {
        //     abort(403, 'You are not authorized to update this event.');
        // }
        $this->authorize('update-review', $review);

        $review->update( 
            $request->validate([
                'service_id' => 'sometimes|exists:services,id',
                'rating' => 'sometimes|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ], $this->validationMessages())
        );

        return new ReviewResource($this->loadRelationships($review));
    }

    /**
     * Delete a Review
     *
     * Delete a specific review by its ID. This action is irreversible.
     *
     * @urlParam id integer required The ID of the review. Example: 1
     * @response 204 {
     *   "message": "Review deleted successfully"
     * }
     */
    public function destroy(string $service, Review $review)
    {
        $this->authorize('delete-review', $review);

        $review->delete();

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

            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',

            'rating.required' => 'The rating is required.',
            'rating.integer' => 'The rating must be a valid integer.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating may not be greater than 5.',

            'comment.string' => 'The comment must be a valid text.',
            'comment.max' => 'The comment may not be greater than 1000 characters.',
        ];
    }
}
