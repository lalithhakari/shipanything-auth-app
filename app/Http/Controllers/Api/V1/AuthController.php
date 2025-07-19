<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\UserLoginRequest;
use App\Http\Requests\Api\V1\Auth\UserRegisterRequest;
use App\Models\User;
use App\Services\V1\Auth\AuthService;
use App\Services\V1\Auth\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class AuthController extends ApiController
{
    public function register(UserRegisterRequest $request, AuthService $authService): JsonResponse
    {
        [$user, $token] = $authService->registerUser(
            name: $request->get('name'),
            email: $request->get('email'),
            password: $request->get('password'),
        );

        return $this->loginResponse(
            message: 'Registered successfully',
            data: [
                'user' => $user,
                'token' => $token,
            ],
            token: $token
        );
    }

    public function login(
        UserLoginRequest $request,
        AuthService $authService
    ): JsonResponse {
        // try {
        [$user, $token] = $authService->loginUser(
            email: $request->get('email'),
            password: $request->get('password'),
        );

        return $this->loginResponse(
            message: 'Logged in successfully',
            data: [
                'user' => $user,
                'token' => $token,
            ],
            token: $token
        );
        // } catch (InvalidArgumentException $e) {
        //     return $this->errorResponse($e->getMessage());
        // } catch (Throwable $th) {
        //     return $this->internalServerErrorResponse();
        // }
    }

    public function logout(Request $request, TokenService $tokenService): JsonResponse
    {
        $tokenService->deleteCurrentToken($request->user());

        return $this->logoutResponse('Logged out successfully');
    }

    /**
     * Get authenticated user details
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * Validate token for other microservices (Internal API)
     * This endpoint will be called by NGINX auth_request
     */
    public function validateToken(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'valid' => false,
                    'message' => 'No token provided'
                ], 401);
            }

            // Find the token in the database
            $accessToken = \Laravel\Passport\Token::where('id', $token)->first();

            if (!$accessToken || $accessToken->revoked || $accessToken->expires_at < now()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            // Get user information
            $user = User::find($accessToken->user_id);

            if (!$user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'User not found'
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token_id' => $accessToken->id,
                'scopes' => $accessToken->scopes ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->token()->revoke();

        // Create new token
        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
