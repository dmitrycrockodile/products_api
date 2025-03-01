<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case for verifying that the index method retrieves reviews.
     * 
     * @return void
     */
    public function test_index_returns_all_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create();

        $response = $this->getJson(route('reviews.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['id' => $reviews->first()->id]);
    }

    /**
     * Test case for verifying that a review can be created successfully.
     * 
     * @return void
     */
    public function test_store_creates_a_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $reviewData = [
            'rating' => 5,
            'title' => 'Amazing product!',
            'body' => 'I love using this product. It exceeded my expectations!',
            'product_id' => $product->id,
        ];

        $response = $this->actingAs($user)->postJson(route('reviews.store'), $reviewData);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'message', 'success']);
        $response->assertJsonFragment(['message' => 'Review successfully added!']);
        $this->assertDatabaseHas('reviews', ['title' => 'Amazing product!']);
    }

    /**
     * Test case for verifying that the store method fails with invalid data.
     * 
     * @return void
     */
    public function test_store_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $invalidData = [
            'rating' => '', 
            'title' => '',
            'body' => 'Too short',
            'product_id' => 9999, 
        ];

        $response = $this->actingAs($user)->postJson(route('reviews.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating', 'title', 'product_id']);
    }

    /**
     * Test case for verifying that a review can be deleted successfully.
     * 
     * @return void
     */
    public function test_destroy_deletes_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson(route('reviews.destroy', $review->id));

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'You deleted your review.']);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * Test case for verifying that a user cannot delete someone else's review.
     * 
     * @return void
     */
    public function test_destroy_fails_when_user_is_not_owner(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $review = Review::factory()->for($anotherUser)->create();

        $response = $this->actingAs($user)->deleteJson(route('reviews.destroy', $review->id));

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'You can delete only your review.']);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
