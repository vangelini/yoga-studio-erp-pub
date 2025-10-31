<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function updateRoleAndStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['Admin', 'Teacher', 'Client'])],
            'status' => ['required', Rule::in(['active', 'pending', 'disabled'])],
        ]);

        return DB::transaction(function () use ($user, $data) {
            $user->update($data);

            if ($data['role'] === 'Teacher') {
                Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'profile_picture_url' => "https://picsum.photos/seed/{$user->id}/100/100",
                        'bio' => 'Welcome! Please update your bio.',
                        'specializations' => [],
                    ]
                );
            }

            return response()->json(['user' => $user->fresh(['teacherProfile'])->makeHidden(['password_hash'])]);
        });
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'newPassword' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password_hash' => Hash::make($data['newPassword']),
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['Admin', 'Teacher', 'Client'])],
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password_hash' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => 'active',
            ]);

            if ($data['role'] === 'Teacher') {
                Teacher::create([
                    'user_id' => $user->id,
                    'profile_picture_url' => "https://picsum.photos/seed/{$user->id}/100/100",
                    'bio' => 'Welcome! Please update your bio.',
                    'specializations' => [],
                ]);
            }

            return response()->json(['user' => $user->makeHidden(['password_hash'])], 201);
        });
    }
}
