<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AuthRepository;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8'
        ]);

        $user = $this->authRepository->register($validated);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $token = $this->authRepository->login($validated);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $this->authRepository->getUser();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token
            ]
        ]);
    }

    public function logout(): JsonResponse
    {
        $success = $this->authRepository->logout();

        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(): JsonResponse
    {
        $user = $this->authRepository->getUser();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => new UserResource($user)
        ]);
    }
}
