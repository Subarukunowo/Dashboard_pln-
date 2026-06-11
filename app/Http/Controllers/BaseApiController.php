<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function success(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function created(mixed $data, string $message = 'Data berhasil dibuat'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(string $message = 'Data berhasil dihapus'): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message], 200);
    }

    protected function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }
}
