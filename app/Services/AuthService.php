<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly AuthRepository $repo) {}

    /**
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User}
     */
    public function login(array $credentials): array
    {
        $user = $this->repo->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Kredensial tidak valid. Silakan cek email dan password Anda.', 401);
        }

        // Generate an API token instead of using stateful sessions
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
