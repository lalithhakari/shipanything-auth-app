<?php

namespace App\Http\Traits\Responses\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponse
{
    public static function loginResponse(string $message = '', array $data = [], string $token = ''): JsonResponse
    {
        $cookie = cookie(
            name: config('session.cookie'),
            value: $token,
            minutes: config('system.auth_cookie_expiry'),
            httpOnly: config('system.auth_cookie_http_only'),
            secure: config('system.auth_cookie_secure'),
            domain: config('session.domain'),
        );

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'errors' => (object) [],
            'data' => (object) $data,
        ], Response::HTTP_OK)->withCookie($cookie);
    }

    public static function logoutResponse(string $message = ''): JsonResponse
    {
        $cookie = cookie(
            name: config('session.cookie'),
            value: null,
            minutes: 0,
            httpOnly: config('system.auth_cookie_http_only'),
            secure: config('system.auth_cookie_secure'),
            domain: config('session.domain'),
        );

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_OK)->withCookie($cookie);
    }

    public static function successResponse(string $message = '', array $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'errors' => (object) [],
            'data' => (object) $data,
        ], Response::HTTP_OK);
    }

    public static function errorResponse(string $message = '', array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => (object) $errors,
            'data' => (object) [],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function splashResponse(): JsonResponse
    {
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * These responses are used for API exceptions
     */
    public static function resourceNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Resource not found',
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_NOT_FOUND);
    }

    public static function httpMethodNotAllowedResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Method not allowed',
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public static function internalServerErrorResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unable to process your request',
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function httpUnauthenticated(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated',
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_UNAUTHORIZED);
    }

    public static function httpUnauthorized(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
            'errors' => (object) [],
            'data' => (object) [],
        ], Response::HTTP_FORBIDDEN);
    }
}
