<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Exception;

class AuthController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $data = $this->service->login($request->only('email', 'password'));
            return $this->successResponse($data, 'Login berhasil. Welcome to DOM HUB.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }
}
