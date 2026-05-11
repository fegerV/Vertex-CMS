<?php

namespace App\Support\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiResponse
{
    public static function success(mixed $data = null, array $meta = [], int $status = 200, array $links = []): JsonResponse
    {
        $payload = [
            'data' => $data,
            'meta' => self::meta($meta),
        ];

        if ($links !== []) {
            $payload['links'] = $links;
        }

        return response()->json($payload, $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, array $data, array $meta = []): JsonResponse
    {
        return self::success($data, array_merge($meta, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'count' => $paginator->count(),
            ],
        ]), 200, [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ]);
    }

    public static function error(string $code, string $message, array $details = [], int $status = 400, array $meta = []): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => self::meta($meta),
        ], $status);
    }

    public static function validation(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return self::error('validation_error', $message, $errors, 422);
    }

    public static function meta(array $extra = []): array
    {
        return array_merge([
            'api_version' => config_value('api.version', 'v1'),
        ], $extra);
    }

    public static function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->is('admin/api/*');
    }
}
