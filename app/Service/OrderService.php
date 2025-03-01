<?php

namespace App\Service;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class OrderService {
   /**
    * Method tries to store the review data into the 'reviews' table
    *
    * @param array $data
    * @return array
   */
   public function store(array $data): array {
      try {
         $user = Auth::user(); 
         if (!$user) {
            return [
               'success' => false, 
               'error' => 'Unauthorized.',
               'status' => Response::HTTP_UNAUTHORIZED,
            ];
         }

         $totalPrice = 0;

         foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $totalPrice += $product->price * $item['quantity'];
         }
         
         $order = $user->orders()->create([
            'total_price' => $totalPrice,
            'ordered_at' => now(),
         ]);
         
         foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            OrderItem::create([
               'order_id' => $order->id,
               'product_id' => $product->id,
               'product_title' => $product->title,
               'quantity' => $item['quantity'],
               'price' => $product->price,
               'subtotal' => $product->price * $item['quantity'],
            ]);
         }
         
         return [
            'success' => true,
            'order' => $order->load('items'),
            'message' => 'Thank you for the order!'
         ];
      } catch (\Exception $e) {
         Log::error('Failed to store the order: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return [
            'success' => false,
            'error' => $e->getMessage(),
            'status' => 500,
         ];
      }
   }
}