<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
