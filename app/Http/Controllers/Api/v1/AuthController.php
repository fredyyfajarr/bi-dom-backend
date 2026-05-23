<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $service) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->service->login($request->credentials());

            return $this->successResponse($data, 'Login berhasil. Welcome to DOM HUB.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the user's current token
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.');
    }
}
