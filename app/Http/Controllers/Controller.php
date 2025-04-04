<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Middleware global yang diterapkan ke semua controller.
     */
    public function __construct()
    {
        // Misalnya, semua endpoint memerlukan autentikasi kecuali yang dikecualikan
        $this->middleware('auth')->except(['publicMethod']);
    }

    /**
     * Helper untuk response JSON standar.
     *
     * @param string $status
     * @param string $message
     * @param mixed|null $data
     * @param int $code
     * @return JsonResponse
     */
    protected function jsonResponse(string $status, string $message, $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Contoh metode publik yang bisa diakses tanpa autentikasi.
     *
     * @return JsonResponse
     */
    public function publicMethod(): JsonResponse
    {
        return $this->jsonResponse('success', 'Public access granted', ['info' => 'This is a public method']);
    }
}
