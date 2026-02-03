<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Get the authenticated user.
     *
     * @return User
     */
    protected function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth()->user();
        return $user;
    }
}
