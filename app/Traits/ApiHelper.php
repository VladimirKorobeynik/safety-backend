<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiHelper {
    
    protected function onSuccess($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function onError(int $code, string $message = ''): JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'code' => $code,
            'message' => $message,
        ], 200);
    }
}