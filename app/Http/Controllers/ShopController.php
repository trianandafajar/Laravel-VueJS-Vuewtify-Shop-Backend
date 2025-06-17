<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ShopRepository;
use App\Repositories\AuthRepository;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    protected $shopRepository;
    protected $authRepository;

    public function __construct(ShopRepository $shopRepository, AuthRepository $authRepository)
    {
        $this->shopRepository = $shopRepository;
        $this->authRepository = $authRepository;
    }

    public function provinces(): JsonResponse
    {
        $provinces = $this->shopRepository->getProvinces();
        
        return response()->json([
            'status' => 'success',
            'data' => $provinces
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|integer'
        ]);

        $cities = $this->shopRepository->getCities($validated['province_id']);
        
        return response()->json([
            'status' => 'success',
            'data' => $cities
        ]);
    }

    public function couriers(): JsonResponse
    {
        $couriers = $this->shopRepository->getCouriers();
        
        return response()->json([
            'status' => 'success',
            'data' => $couriers
        ]);
    }

    public function shipping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer',
            'courier' => 'required|string'
        ]);

        $shipping = $this->shopRepository->calculateShipping($validated);
        
        return response()->json([
            'status' => 'success',
            'data' => $shipping
        ]);
    }

    public function payment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.book_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $user = $this->authRepository->getUser();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        $order = $this->shopRepository->createOrder($validated, $user);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }

    public function myOrder(): JsonResponse
    {
        $user = $this->authRepository->getUser();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        $orders = $this->shopRepository->getUserOrders($user);
        
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
}