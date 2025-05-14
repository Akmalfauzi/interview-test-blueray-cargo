<?php

namespace App\Services\V1\API\User;

use App\Models\User;

class UserService
{
    public function getAllUsers()
    {
        return User::all();
    }
}