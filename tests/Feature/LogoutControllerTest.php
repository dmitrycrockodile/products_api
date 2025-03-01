<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
   use RefreshDatabase;

   /**
    * Test case for verifying that an authenticated user can log out successfully.
    * 
    * This test checks that:
    * - An authenticated user can log out.
    * - The response has the correct structure and status.
    * 
    * @return void
    */
   public function test_authenticated_user_can_logout(): void
   {
      $user = User::factory()->create();

      Sanctum::actingAs($user);

      $response = $this->postJson('/api/logout');

      $response->assertStatus(200);
      $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
   }

   /**
    * Test case for verifying that a non-authenticated user cannot log out.
    * 
    * This test checks that:
    * - A guest user cannot perform a logout action.
    * - The response follows the correct structure and status.
    * 
    * @return void
   */
   public function test_guest_user_cannot_logout(): void
   {
      $response = $this->postJson('/api/logout');

      $response->assertStatus(Response::HTTP_UNAUTHORIZED);
      $response->assertJsonFragment(['message' => 'Unauthenticated.']);
   }
}