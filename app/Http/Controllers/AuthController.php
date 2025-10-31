<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => "Your account is currently {$user->status}. Please contact an administrator.",
            ], 403);
        }

        if (!Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $safeUser = $user->makeHidden(['password_hash'])->toArray();

        return response()->json([
            'user' => $safeUser,
        ]);
    }
}
