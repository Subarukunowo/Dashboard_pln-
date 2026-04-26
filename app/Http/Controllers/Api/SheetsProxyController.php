<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleSheetsProxyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SheetsProxyController extends Controller
{
    public function __construct(
        protected ?GoogleSheetsProxyService $proxy = null
    ) {}

    public function query(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:data,historis',
                'action' => 'required|string',
                'payload' => 'nullable|array',
            ]);

            $fullPayload = array_merge(
                $validated['payload'] ?? [],
                [
                    'action' => $validated['action'],
                    '_meta' => [
                        'timestamp' => now()->toISOString(),
                        'ip' => $request->ip(),
                        'origin' => $request->headers->get('Origin'),
                    ]
                ]
            );

            $result = $this->proxy 
                ? $this->proxy->fetch($validated['type'], $fullPayload, $request->ip())
                : ['dummy' => true, 'message' => 'Service not bound', 'data' => []];

            return response()->json([
                'success' => true,
                'data' => $result,
                'meta' => ['action' => $validated['action'], 'type' => $validated['type']]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'details' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('SheetsProxy failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'proxy_error',
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to fetch data',
            ], 500);
        }
    }
}