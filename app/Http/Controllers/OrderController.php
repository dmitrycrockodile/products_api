<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Http\Requests\Order\StoreRequest;
use Illuminate\Http\JsonResponse;
use App\Service\OrderService;
use Illuminate\Http\Response;

class OrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse {
        $orders = auth()->user()->orders()->with('items.product')->latest()->get();
        
        return response()->json([
            'success' => true,
            'orders' => OrderResource::collection($orders)
        ]);
    }

    public function store(StoreRequest $request) {
        $data = $request->validated();
        $response = $this->orderService->store($data);

        if (!$response['success']) {
            return $this->errorResponse($response['error'], $response['status']);
        }

        return $this->successResponse([
            'order' => new OrderResource($response['order']),
        ], $response['message'], Response::HTTP_CREATED);
    }
}