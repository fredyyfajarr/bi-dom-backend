<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthService
{
    protected $repo;

    public function __construct(AuthRepository $repo)
    {
        $this->repo = $repo;
    }

    public function login(array $credentials)
    {
        $user = $this->repo->findByEmail($credentials['email']);

        // Jika user tidak ada atau password salah, lempar error
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Kredensial tidak valid. Silakan cek email dan password Anda.', 401);
        }

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
