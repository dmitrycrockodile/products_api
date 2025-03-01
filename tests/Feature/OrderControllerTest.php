<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case for verifying that a user can place an order successfully.
     *
     * @return void
     */
    public function test_user_can_place_an_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $orderData = [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('orders.store'), $orderData);
  
        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'message', 'success']);
        $response->assertJsonFragment(['message' => 'Thank you for the order!']);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /**
     * Test case for verifying that order creation fails with invalid data.
     *
     * @return void
     */
    public function test_order_creation_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $invalidData = [
            'items' => [
                ['product_id' => null, 'quantity' => 0] // Invalid product_id and quantity
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('orders.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.product_id', 'items.0.quantity']);
    }

    /**
     * Test case for verifying that a user can view their order history.
     *
     * @return void
     */
    public function test_user_can_view_their_order_history(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50]);
        $productQuantity = 2;
        $order = Order::factory()->create(['user_id' => $user->id, 'total_price' => $productQuantity * $product->price]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 50,
            'quantity' => $productQuantity,
            'product_title' => $product->title,
            'subtotal' => $product->price * $productQuantity,
        ]);

        $response = $this->actingAs($user)->getJson(route('orders.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['orders', 'success']);
        $response->assertJsonFragment(['id' => $order->id]);
        $response->assertJsonPath('orders.0.total_price', 100);
    }

    /**
     * Test case for verifying that a user cannot see other users' orders.
     *
     * @return void
     */
    public function test_user_cannot_view_other_users_orders(): void
    {
        $user1 = User::factory()->create();
        $product = Product::factory()->create(['price' => 50]);
        $user2 = User::factory()->create();
        $productQuantity = 2;
        $order = Order::factory()->create(['user_id' => $user1->id, 'total_price' => $productQuantity * $product->price]);

        $response = $this->actingAs($user2)->getJson(route('orders.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'orders'); 
    }
}
