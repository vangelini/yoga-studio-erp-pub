<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordUpdateController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['Client', 'Teacher']), 403);

        return view('account.password');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['Client', 'Teacher']), 403);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => __('Le password non coincidono.'),
        ]);

        if (!Hash::check($data['current_password'], $user->password_hash)) {
            return back()
                ->withErrors(['current_password' => __('La password attuale non è corretta.')])
                ->withInput();
        }

        $user->forceFill([
            'password_hash' => Hash::make($data['password']),
        ])->save();

        return redirect()->route('dashboard')->with('status', __('Password aggiornata con successo.'));
    }
}
