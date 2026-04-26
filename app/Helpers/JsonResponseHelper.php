<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class JsonResponseHelper
{
    /**
     * success
     *
     * @param  mixed  $message
     * @param  mixed  $redirectUrl
     * @param  mixed  $data
     * @param  mixed  $code
     */
    public static function success(string $message, $data = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    /**
     * fail
     *
     * @param  mixed  $message
     * @param  mixed  $code
     * @param  mixed  $errors
     */
    public static function fail(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * error
     *
     * @param  mixed  $th
     * @param  mixed  $code
     * @param  mixed  $errors
     */
    public static function error($th, int $code = 404, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $th->getMessage() ?? __('Terjadi kesalahan saat menyimpan data ke sistem'),
            'timestamp' => now()->toIso8601String(),
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * serverError
     *
     * @param  mixed  $th
     * @param  mixed  $code
     * @param  mixed  $errors
     */
    public static function serverError(\Throwable $th, int $code = 400, $errors = null): JsonResponse
    {
        return self::error($th, $code, $errors);
    }

    /**
     * validationError
     *
     * @param  mixed  $th
     * @param  mixed  $code
     * @param  mixed  $message
     */
    public static function validationError($th, int $code = 422, ?string $message = null): JsonResponse
    {
        return response()->json(
            [
                'success' => false,
                'errors' => $th->errors(),
                'message' => $message ?? __('Invalid Parameter'),
                'timestamp' => now()->toIso8601String(),
            ],
            $code
        );
    }
}
