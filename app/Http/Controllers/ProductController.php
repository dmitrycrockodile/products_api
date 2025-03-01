<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Filters\ProductFilter;
use App\Http\Requests\Product\IndexRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Service\ProductService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends BaseController
{
   protected ProductService $productService;

   public function __construct(ProductService $productService) {
      $this->productService = $productService;
   }

   /**
    * Retrieves the products.
    * 
    * @param IndexRequest A request with filters to apply (if needed)
    * 
    * @return JsonResponse A JSON response containing retrieved products.
   */
   public function index(IndexRequest $request): JsonResponse {
      try {
         $data = $request->validated();
         $filter = app()->make(ProductFilter::class, ['queryParams' => $data]);
         $products = Product::filter($filter)->get();

         return $this->successResponse(ProductResource::collection($products));
      } catch (\Exception $e) {
         Log::error('Failed to retrieve products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }

   /**
    * Stores the product.
    * 
    * @param StoreRequest $request A request with product data
    * 
    * @return JsonResponse A JSON response containing newly created product or error info.
   */
   public function store(StoreRequest $request): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->store($data);
      
      if (!$serviceResponse['success']) {
         return $this->errorResponse($serviceResponse['message'], $serviceResponse['status']);
      }

      return $this->successResponse($serviceResponse['product'], $serviceResponse['message'], Response::HTTP_CREATED);
   }

   /**
    * Updates the product according to new data.
    * 
    * @param StoreRequest $request A request with new product data
    * @param Product $product Instance of the product to update
    * 
    * @return JsonResponse A JSON response containing newly created product or error info.
   */
   public function update(StoreRequest $request, Product $product): JsonResponse {
      $data = $request->validated();
      $serviceResponse = $this->productService->update($data, $product);

      if (!$serviceResponse['success']) {
         return $this->errorResponse($serviceResponse['message'], $serviceResponse['status']);
      }

      return $this->successResponse($serviceResponse['product'], $serviceResponse['message']);
   }

   /**
    * Deletes the product.
    * 
    * @param Product $product Instance of the product to delete
    * 
    * @return JsonResponse A JSON response containing newly created product.
   */
   public function destroy($id): JsonResponse {
      try {
         $product = Product::findOrFail($id);

         $product->delete();
         return $this->successResponse([], 'Product successfully deleted.');
      } catch (ModelNotFoundException $e) {
         return $this->errorResponse('Failed to find the product.', Response::HTTP_NOT_FOUND);
      } catch (\Exception $e) {
         Log::error('Failed to retrieve products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);
         return $this->errorResponse('Failed to delete the product.', Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }
}