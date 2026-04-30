<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }
}
