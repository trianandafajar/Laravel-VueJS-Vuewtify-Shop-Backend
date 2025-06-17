<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->model->create($data);
    }

    public function login(array $credentials): ?string
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return $user->createToken('auth_token')->plainTextToken;
        }
        return null;
    }

    public function logout(): bool
    {
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
            return true;
        }
        return false;
    }

    public function getUser(): ?User
    {
        return Auth::user();
    }
} 