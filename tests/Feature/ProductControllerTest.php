<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case for verifying that the index method retrieves products without filters.
     * 
     * @return void
     */
    public function test_index_returns_all_products_without_filters(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $response = $this->getJson(route('products.index'));

        $response->assertStatus(200);
        
        $response->assertJsonCount(2, 'data'); 
        $response->assertJsonFragment(['id' => $product1->id]);
        $response->assertJsonFragment(['id' => $product2->id]);
    }

    /**
     * Test case for verifying filtering products by category.
     * 
     * @return void
     */
    public function test_index_filters_products_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->count(2)->create(['category_id' => $category1->id]);
        Product::factory()->count(1)->create(['category_id' => $category2->id]);

        $response = $this->getJson(route('products.index', ['categories' => [$category1->id]]));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test case for verifying filtering products by price range.
     * 
     * @return void
     */
    public function test_index_filters_products_by_price_range(): void
    {
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 200]);

        $response = $this->getJson(route('products.index', ['prices' => [50, 150]]));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data'); // Should return products with price 50 and 100
    }

    /**
     * Test case for verifying filtering products by title.
     * 
     * @return void
     */
    public function test_index_filters_products_by_title(): void
    {
        Product::factory()->create(['title' => 'Amazing Product']);
        Product::factory()->create(['title' => 'Another Item']);
        Product::factory()->create(['title' => 'Cool Gadget']);

        $response = $this->getJson(route('products.index', ['title' => 'Amazing']));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data'); // Should return only "Amazing Product"
    }

    /**
     * Test case for verifying sorting products by price in ascending order.
     * 
     * @return void
     */
    public function test_index_sorts_products_by_price_ascending(): void
    {
        Product::factory()->create(['price' => 300]);
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 200]);

        $response = $this->getJson(route('products.index', ['sortby' => 'price(ASC)']));
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.price', 100);
        $response->assertJsonPath('data.1.price', 200);
        $response->assertJsonPath('data.2.price', 300);
    }

    /**
     * Test case for verifying sorting products by price in descending order.
     * 
     * @return void
     */
    public function test_index_sorts_products_by_price_descending(): void
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 200]);
        Product::factory()->create(['price' => 300]);

        $response = $this->getJson(route('products.index', ['sortby' => 'price(DESC)']));

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.price', 300);
        $response->assertJsonPath('data.1.price', 200);
        $response->assertJsonPath('data.2.price', 100);
    }

    /**
     * Test case for verifying filtering high-rated products.
     * 
     * @return void
     */
    public function test_index_filters_high_rated_products(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Review::factory()->count(3)->create(['product_id' => $product1->id, 'rating' => 5]);
        Review::factory()->count(3)->create(['product_id' => $product2->id, 'rating' => 3]);

        $response = $this->getJson(route('products.index', ['highRated' => true]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data'); 
        $response->assertJsonFragment(['id' => $product1->id]);
    }

    /**
     * Test case for verifying that a product can be created successfully.
     * 
     * @return void
     */
    public function test_store_creates_a_product(): void
    {        
        $category = Category::factory()->create();

        $productData = [
            'title' => 'New Product',
            'description' => 'This is a description for the new product',
            'preview_image' => UploadedFile::fake()->image('product.jpg'),
            'price' => 100,
            'old_price' => 150,
            'count' => 50,
            'category_id' => $category->id,
        ];
        
        $response = $this->postJson(route('products.store'), $productData);
        
        $response->assertStatus(201);
        
        $response->assertJsonStructure([
            'data',
            'message',
            'success'
        ]);
        $response->assertJsonFragment(['message' => 'Successfully created the product!']);
        $response->assertJsonFragment(['title' => 'New Product']);
    }

    /**
     * Test case for verifying that the store method fails with invalid data.
     * 
     * @return void
     */
    public function test_store_fails_with_invalid_data(): void
    {
        $invalidData = [
            'title' => '', 
            'description' => 'Short description',
            'preview_image' => 'not_an_image', 
            'price' => -100, 
            'old_price' => -50, 
            'count' => -10, 
            'category_id' => 9999, 
        ];

        $response = $this->postJson(route('products.store'), $invalidData);

        $response->assertStatus(422); 
        $response->assertJsonFragment(['title' => ['Please write the title']]);
    }

    /**
     * Test case for verifying that the update method updates product details.
     * 
     * @return void
     */
    public function test_update_updates_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $updatedData = [
            'title' => 'Updated Product',
            'description' => 'Updated description',
            'preview_image' => UploadedFile::fake()->image('updated_product.jpg'),
            'price' => 200,
            'old_price' => 250,
            'count' => 75,
            'category_id' => $category->id,
        ];
   
        $response = $this->putJson(route('products.update', $product->id), $updatedData);
        
        $response->assertStatus(200);
        
        $response->assertJsonFragment(['message' => 'Successfully updated the product!']);
        $response->assertJsonFragment(['title' => 'Updated Product']);
    }

    /**
     * Test case for verifying that the update method fails with invalid data.
     * 
     * @return void
     */
    public function test_update_fails_with_invalid_data(): void
    {        
        $product = Product::factory()->create();
        
        $invalidData = [
            'title' => '', 
            'description' => 'Updated description',
            'preview_image' => 'not_an_image', 
            'price' => -50, 
            'old_price' => -10, 
            'count' => -5, 
            'category_id' => 9999, 
        ];
        
        $response = $this->putJson(route('products.update', $product->id), $invalidData);
   
        $response->assertStatus(422); 
    }

    /**
     * Test case for verifying that a product can be deleted successfully.
     * 
     * @return void
     */
    public function test_destroy_deletes_product(): void
    {        
        $product = Product::factory()->create();
        
        $response = $this->deleteJson(route('products.destroy', $product->id));
       
        $response->assertStatus(200); 
        $response->assertJsonFragment(['message' => 'Product successfully deleted.']);
    }

    /**
     * Test case for verifying that attempting to delete a non-existent product returns an error.
     * 
     * @return void
     */
    public function test_destroy_fails_when_product_not_found(): void
    {
        $nonExistentProductId = 999;
        
        $response = $this->deleteJson(route('products.destroy', $nonExistentProductId));

        $response->assertStatus(404); 
        $response->assertJsonFragment(['message' => 'Failed to find the product.']);
    }
}
