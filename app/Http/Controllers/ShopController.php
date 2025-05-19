<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Book;
use App\Models\Order;
use App\Models\BookOrder;
use App\Models\Province;
use App\Models\City;
use App\Http\Resources\Provinces as ProvinceResourceCollection;
use App\Http\Resources\Cities as CityResourceCollection;

class ShopController extends Controller
{
    public function provinces()
    {
        return new ProvinceResourceCollection(Province::all());
    }

    public function cities()
    {
        return new CityResourceCollection(City::all());
    }

    public function shipping(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $user->update($request->only(['name', 'address', 'phone', 'province_id', 'city_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Shipping information updated successfully',
            'data' => $user
        ]);
    }

    public function couriers()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Available couriers',
            'data' => [
                ['id' => 'jne', 'text' => 'JNE'],
                ['id' => 'tiki', 'text' => 'TIKI'],
                ['id' => 'pos', 'text' => 'POS'],
            ]
        ]);
    }

    protected function getServices($data)
    {
        $apiKey = env('RAJAONGKIR_API_KEY');
        $url = "https://api.rajaongkir.com/starter/cost";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded",
                "key: " . $apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        return ['error' => $error, 'response' => $response];
    }

    public function services(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'courier' => 'required',
            'carts' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $destination = $user->city_id;
        if (!$destination) {
            return response()->json(['status' => 'error', 'message' => 'Destination not set'], 400);
        }

        $origin = 153; // Jakarta Selatan (bisa dipindah ke .env jika perlu)
        $courier = $request->courier;
        $carts = json_decode($request->carts, true);

        $totalWeight = array_reduce($carts, fn($carry, $item) => $carry + (Book::find($item['id'])->weight * $item['quantity'] ?? 0), 0);

        if ($totalWeight <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Invalid cart weight'], 400);
        }

        $response = $this->getServices([
            "origin" => $origin,
            "destination" => $destination,
            "weight" => $totalWeight * 1000,
            "courier" => $courier
        ]);

        if ($response['error']) {
            return response()->json(['status' => 'error', 'message' => 'Courier service unavailable'], 500);
        }

        $costs = json_decode($response['response'])->rajaongkir->results[0]->costs ?? [];
        $services = array_map(fn($cost) => [
            'service' => $cost->service,
            'cost' => $cost->cost[0]->value,
            'estimation' => str_replace('hari', '', trim($cost->cost[0]->etd)),
        ], $costs);

        return response()->json([
            'status' => 'success',
            'message' => count($services) > 0 ? 'Courier services available' : 'No services available',
            'data' => $services
        ]);
    }

    public function myOrders()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User orders retrieved',
            'data' => $user->orders()->latest()->get()
        ]);
    }
}