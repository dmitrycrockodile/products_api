<?php

namespace App\Service;

use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class ReviewService {
   /**
    * Method tries to store the review data into the 'reviews' table
    *
    * @param array $data
    * @return array
   */
   public function store(array $data): array {
      try {
         $userId = Auth::id(); 
         if (!$userId) {
            return [
               'success' => false, 
               'error' => 'Unauthorized.',
               'status' => Response::HTTP_UNAUTHORIZED,
            ];
         }

         $review = Review::create([
            'user_id' => $userId,
            ...$data,
         ]);
         $product = Product::where('id', $data['product_id'])->first();
         return [
            'success' => true,
            'review' => $review,
            'average_rating' => $product->averageRating
         ];
      } catch (\Exception $e) {
         Log::error('Failed to update the review: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return [
            'success' => false,
            'error' => 'Failed to create the review, please try again.',
            'status' => 500,
         ];
      }
   }
}