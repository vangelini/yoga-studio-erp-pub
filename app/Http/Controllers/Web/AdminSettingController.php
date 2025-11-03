<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        $settings = Setting::query()
            ->whereIn('key', [
                'membership_fee',
                'membership_auto_generate',
                'receipt_owner_password',
                'receipt_user_password_mode',
                'receipt_user_password_custom',
            ])
            ->pluck('value', 'key');

        return view('dashboard.settings', [
            'membership_fee' => $settings['membership_fee'] ?? 20,
            'membership_auto_generate' => isset($settings['membership_auto_generate']) ? (bool) $settings['membership_auto_generate'] : false,
            'receipt_owner_password' => $settings['receipt_owner_password'] ?? '',
            'receipt_user_password_mode' => $settings['receipt_user_password_mode'] ?? 'blank',
            'receipt_user_password_custom' => $settings['receipt_user_password_custom'] ?? '',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'membership_fee' => ['required', 'numeric', 'min:0'],
            'membership_auto_generate' => ['nullable', 'boolean'],
            'receipt_owner_password' => ['nullable', 'string', 'max:255'],
            'receipt_user_password_mode' => ['required', Rule::in(['blank', 'email', 'custom'])],
            'receipt_user_password_custom' => ['nullable', 'string', 'max:255', 'required_if:receipt_user_password_mode,custom'],
        ], [
            'receipt_user_password_custom.required_if' => 'Inserisci la password personalizzata quando scegli la modalità "Password personalizzata".',
        ]);

        $settingsToPersist = [
            'membership_fee' => (string) $data['membership_fee'],
            'membership_auto_generate' => $request->boolean('membership_auto_generate') ? '1' : '0',
            'receipt_owner_password' => trim((string) ($data['receipt_owner_password'] ?? '')),
            'receipt_user_password_mode' => $data['receipt_user_password_mode'],
            'receipt_user_password_custom' => $data['receipt_user_password_mode'] === 'custom'
                ? trim((string) $data['receipt_user_password_custom'])
                : '',
        ];

        foreach ($settingsToPersist as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('status', 'Impostazioni aggiornate con successo.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
