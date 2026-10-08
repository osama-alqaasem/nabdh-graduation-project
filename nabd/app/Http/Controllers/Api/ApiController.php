<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    protected function currentUser(Request $request): ?User
    {
        return $request->user(); // ✅ Sanctum
    }

    protected function requireUser(Request $request): User
    {
        $user = $request->user();

        abort_if(!$user, 401, 'Unauthorized');

        return $user;
    }
}