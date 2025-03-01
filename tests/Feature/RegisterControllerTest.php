<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
   use RefreshDatabase;

   /**
    * Test case for verifying that the user can register.
    * 
    * This test checks that:
    * - User can register himself.
    * - The returned JSON response follows the correct structure and status.
    * 
    * @return void
    */
   public function test_user_can_register(): void
   {
      $userData = [
         'name' => 'Test name',
         'email' => 'test@example.com',
         'password' => 'SecurePass123!',
         'password_confirmation' => 'SecurePass123!',
      ];

      $response = $this->postJson('/api/register', $userData);

      $response->assertStatus(201); 
      $response->assertJsonStructure([
         'data',
         'success',
         'message'
      ]);
      $response->assertJsonFragment(['message' => 'User registered successfully.']);
   }

   /**
    * Test case for verifying that the user can not register with invalid data.
    * 
    * This test checks that:
    * - User can not register with invalid data.
    * - The returned JSON response follows the correct structure and status.
    * 
    * @return void
    */
   public function test_user_registration_fails_with_invalid_data(): void
   {
      $userData = [
         'name' => '',
         'email' => 'invalid-email',
         'password' => '123',
         'password_confirmation' => '456',
      ];

      $response = $this->postJson('/api/register', $userData);
      $response->assertStatus(422);
      $response->assertJsonStructure([
         'errors',
         'message'
      ]);
   }

   /**
    * Test case for verifying that the user can not register two times with the same email.
    * 
    * This test checks that:
    * - User can not register with registered email.
    * - The returned JSON response follows the correct structure and status.
    * 
    * @return void
    */
   public function test_user_registration_fails_with_registered_email(): void
   {
      $userData = [
         'name' => 'Test name',
         'email' => 'test@example.com',
         'password' => 'SecurePass123!',
         'password_confirmation' => 'SecurePass123!',
      ];
      $this->postJson('/api/register', $userData);
      $repeatedResponse = $this->postJson('/api/register', $userData);

      $repeatedResponse->assertStatus(422);
      $repeatedResponse->assertJsonStructure([
         'errors',
         'message'
      ]);
      $repeatedResponse->assertJsonFragment(['message' => 'This email is already in use']);
   }
}