<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Review\StoreRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use App\Service\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ReviewController extends BaseController
{
   protected ReviewService $reviewService;

   public function __construct(ReviewService $reviewService) {
      $this->reviewService = $reviewService;
   }

   /**
     * Retrieves all reviews.
     *
     * @return JsonResponse A JSON response confirming the deletion.
   */
   public function index(): JsonResponse {
      try {
         $reviews = Review::all();

         return $this->successResponse(ReviewResource::collection($reviews));
      } catch (\Exception $e) {
         Log::error('Failed to retrieve products: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
         ]);

         return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }

   /**
     * Stores a new review.
     *
     * Validates the request, processes the review through the ReviewService,
     * and returns the created review along with the updated average rating.
     *
     * @param StoreRequest $request The validated request containing review data.
     * @return JsonResponse A JSON response with the newly created review and average rating.
   */
   public function store(StoreRequest $request): JsonResponse {
      $data = $request->validated();
      $response = $this->reviewService->store($data);

      if (!$response['success']) {
         return $this->errorResponse($response['error'], $response['status']);
      }

      return $this->successResponse([
         'review' => new ReviewResource($response['review']),
         'average_rating' => $response['average_rating']
      ], 'Review successfully added!', Response::HTTP_CREATED);
   }
   
   /**
     * Deletes a review.
     *
     * Checks if the user has permission to delete the review. If authorized,
     * the review is removed from the database.
     *
     * @param Review $review The review to be deleted.
     * @return JsonResponse A JSON response confirming the deletion.
   */
   public function destroy(Review $review): JsonResponse {
      // Permissions check
      if (Gate::denies('delete', $review)) {
         return $this->errorResponse('You can delete only your review.', Response::HTTP_UNAUTHORIZED);
      }

      $review->delete();

      return $this->successResponse([], 'You deleted your review.');
   }
}