<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ShopRepository
{
    protected $orderModel;
    protected $userModel;
    protected $rajaOngkirKey;
    protected $rajaOngkirUrl;

    public function __construct(Order $orderModel, User $userModel)
    {
        $this->orderModel = $orderModel;
        $this->userModel = $userModel;
        $this->rajaOngkirKey = config('services.rajaongkir.key');
        $this->rajaOngkirUrl = config('services.rajaongkir.url');
    }

    public function getProvinces(): array
    {
        $response = Http::withHeaders([
            'key' => $this->rajaOngkirKey
        ])->get($this->rajaOngkirUrl . '/province');

        return $response->json()['rajaongkir']['results'] ?? [];
    }

    public function getCities(int $provinceId): array
    {
        $response = Http::withHeaders([
            'key' => $this->rajaOngkirKey
        ])->get($this->rajaOngkirUrl . '/city', [
            'province' => $provinceId
        ]);

        return $response->json()['rajaongkir']['results'] ?? [];
    }

    public function getCouriers(): array
    {
        return [
            ['code' => 'jne', 'name' => 'JNE'],
            ['code' => 'pos', 'name' => 'POS Indonesia'],
            ['code' => 'tiki', 'name' => 'TIKI']
        ];
    }

    public function calculateShipping(array $data): array
    {
        $response = Http::withHeaders([
            'key' => $this->rajaOngkirKey
        ])->post($this->rajaOngkirUrl . '/cost', [
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'weight' => $data['weight'],
            'courier' => $data['courier']
        ]);

        return $response->json()['rajaongkir']['results'] ?? [];
    }

    public function createOrder(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $order = $this->orderModel->create([
                'user_id' => $user->id,
                'invoice_number' => 'INV-' . time(),
                'total_amount' => $data['total_amount'],
                'status' => 'pending'
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            return $order;
        });
    }

    public function getUserOrders(User $user): array
    {
        return $this->orderModel
            ->with(['items.book'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
} 