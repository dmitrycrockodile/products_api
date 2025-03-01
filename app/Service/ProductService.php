<?php

namespace App\Service;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductService {
   /**
    * Store a new product.
    *
    * @param array $data
    *
    * @return array
    */
   public function store(array $data): array {
      try {
         // Store preview image and add file path to data
         $data['preview_image'] = $this->storePreviewImage($data['preview_image']);

         $product = Product::firstOrCreate([
            'title' => $data['title'],
         ], $data);

         return $this->successResponse($product, 'Successfully created the product!');
      } catch (\Exception $e) {
         
         Log::error('Failed to store the product: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return $this->errorResponse('Failed to store the product, please try again.', $e);
     }
   } 

   /**
    * Update the product.
    *
    * @param array $data
    * @param Product $product
    *
    * @return array
    */
   public function update(array $data, Product $product): array {
      try {
         // Handle preview image update
         $data['preview_image'] = isset($data['preview_image'])
            ? $this->storePreviewImage($data['preview_image'])
            : $product->preview_image;

         $product->update($data);

         return $this->successResponse($product, 'Successfully updated the product!');
      } catch (\Exception $e) {
         Log::error('Failed to update cart item: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString() 
         ]);

         return $this->errorResponse('Failed to update the product, please try again.', $e);
      }
   }

   /**
    * Store the preview image and return its path.
    *
    * @param mixed $image
    * @return string
    */
   private function storePreviewImage($image): string {
      return Storage::disk('public')->put('/images', $image);
   }

   /**
    * Success response formatting.
    *
    * @param Product $product
    * @param string $message
    * @return array
    */
    private function successResponse(Product $product, string $message): array {
      return [
         'success' => true,
         'product' => new ProductResource($product),
         'message' => $message,
      ];
   }

   /**
    * Error response formatting.
    *
    * @param string $errorMessage
    * @param Exception $exception
    * @return array
    */
   private function errorResponse(string $errorMessage, \Exception $exception): array {
      // Log error with the exception's context
      Log::error($errorMessage, [
         'exception' => $exception->getMessage(),
         'trace' => $exception->getTraceAsString(),
      ]);

      return [
         'success' => false,
         'message' => $errorMessage,
         'status' => 500,
      ];
   }
}