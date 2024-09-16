<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_fetch_a_list_of_services()
    {
        Category::factory()->count(5)->create();
        Service::factory()->count(5)->create();

        $response = $this->getJson('/api/services');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Test fetching a list of services with included relationships.
     *
     * @return void
     */
    public function test_can_fetch_list_of_services_with_includes()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $service = Service::factory()
            ->has(Review::factory()->count(2)->state(['user_id' => $user->id]))
            ->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/services?include=category,reviews,reviews.user');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $service->id,
            'name' => $service->name,
            'category' => [
                'created_at' => $category->created_at,
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'updated_at' => $category->updated_at
            ],
            'reviews' => $service->reviews->map(function ($review) {
                return [
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'updated_at' => $review->updated_at,
                    'user' => [
                        "created_at" => $review->user->created_at,
                        'email' => $review->user->email,
                        "email_verified_at" => $review->user->email_verified_at,
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        "updated_at" => $review->updated_at
                    ]
                ];
            })->toArray(),
        ]);

    }

    /**
     * Test destroying a service.
     *
     * @return void
     */
    public function test_can_destroy_service()
    {
        $service = Service::factory()->create();

        $response = $this->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(204); // No content
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

}
