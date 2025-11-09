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
                'membership_morosita_page_size',
                'receipt_owner_password',
                'receipt_user_password_mode',
                'receipt_user_password_custom',
                'course_payment_auto_generate',
                'course_payment_lead_days',
                'course_payment_last_run',
                'membership_last_run',
                'extra_day_enabled',
            ])
            ->pluck('value', 'key');

        $coursePaymentLastRun = [];
        if (!empty($settings['course_payment_last_run'])) {
            $decoded = json_decode($settings['course_payment_last_run'], true);
            if (is_array($decoded)) {
                $coursePaymentLastRun = $decoded;
            }
        }

        return view('dashboard.settings', [
            'membership_fee' => $settings['membership_fee'] ?? 20,
            'membership_auto_generate' => isset($settings['membership_auto_generate']) ? (bool) $settings['membership_auto_generate'] : false,
            'membership_morosita_page_size' => isset($settings['membership_morosita_page_size']) ? (int) $settings['membership_morosita_page_size'] : 5,
            'receipt_owner_password' => $settings['receipt_owner_password'] ?? '',
            'receipt_user_password_mode' => $settings['receipt_user_password_mode'] ?? 'blank',
            'receipt_user_password_custom' => $settings['receipt_user_password_custom'] ?? '',
            'course_payment_auto_generate' => isset($settings['course_payment_auto_generate']) ? (bool) $settings['course_payment_auto_generate'] : false,
            'course_payment_lead_days' => isset($settings['course_payment_lead_days']) ? (int) $settings['course_payment_lead_days'] : 10,
            'course_payment_last_run' => $coursePaymentLastRun,
            'extra_day_enabled' => isset($settings['extra_day_enabled']) ? (bool) $settings['extra_day_enabled'] : false,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'membership_fee' => ['required', 'numeric', 'min:0'],
            'membership_auto_generate' => ['nullable', 'boolean'],
            'membership_morosita_page_size' => ['required', 'integer', 'min:1', 'max:50'],
            'receipt_owner_password' => ['nullable', 'string', 'max:255'],
            'receipt_user_password_mode' => ['required', Rule::in(['blank', 'email', 'custom'])],
            'receipt_user_password_custom' => ['nullable', 'string', 'max:255', 'required_if:receipt_user_password_mode,custom'],
            'course_payment_auto_generate' => ['nullable', 'boolean'],
            'course_payment_lead_days' => ['required', 'integer', 'min:1', 'max:120'],
            'extra_day_enabled' => ['nullable', 'boolean'],
        ], [
            'receipt_user_password_custom.required_if' => 'Inserisci la password personalizzata quando scegli la modalità "Password personalizzata".',
        ]);

        $settingsToPersist = [
            'membership_fee' => (string) $data['membership_fee'],
            'membership_auto_generate' => $request->boolean('membership_auto_generate') ? '1' : '0',
            'membership_morosita_page_size' => (string) $data['membership_morosita_page_size'],
            'receipt_owner_password' => trim((string) ($data['receipt_owner_password'] ?? '')),
            'receipt_user_password_mode' => $data['receipt_user_password_mode'],
            'receipt_user_password_custom' => $data['receipt_user_password_mode'] === 'custom'
                ? trim((string) $data['receipt_user_password_custom'])
                : '',
            'course_payment_auto_generate' => $request->boolean('course_payment_auto_generate') ? '1' : '0',
            'course_payment_lead_days' => (string) $data['course_payment_lead_days'],
            'extra_day_enabled' => $request->boolean('extra_day_enabled') ? '1' : '0',
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
