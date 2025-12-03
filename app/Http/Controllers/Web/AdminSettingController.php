<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MembershipSubscription;
use App\Services\MembershipManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'membership_expiry_mode',
                'membership_academic_start_date',
                'membership_academic_end_date',
                'receipt_owner_password',
                'receipt_user_password_mode',
                'receipt_user_password_custom',
                'course_payment_auto_generate',
                'course_payment_lead_days',
                'course_payment_last_run',
                'membership_last_run',
                'extra_day_enabled',
                'private_lessons_enabled',
                'notification_overdue_days',
                'notification_overdue_message',
                'notification_pending_message',
                'bank_transfer_info_message',
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
            'membership_expiry_mode' => $settings['membership_expiry_mode'] ?? 'academic',
            'membership_academic_start_date' => $settings['membership_academic_start_date'] ?? null,
            'membership_academic_end_date' => $settings['membership_academic_end_date'] ?? null,
            'course_payment_auto_generate' => isset($settings['course_payment_auto_generate']) ? (bool) $settings['course_payment_auto_generate'] : false,
            'course_payment_lead_days' => isset($settings['course_payment_lead_days']) ? (int) $settings['course_payment_lead_days'] : 10,
            'course_payment_last_run' => $coursePaymentLastRun,
            'extra_day_enabled' => isset($settings['extra_day_enabled']) ? (bool) $settings['extra_day_enabled'] : false,
            'private_lessons_enabled' => isset($settings['private_lessons_enabled']) ? (bool) $settings['private_lessons_enabled'] : false,
            'notification_overdue_days' => isset($settings['notification_overdue_days']) ? (int) $settings['notification_overdue_days'] : 7,
            'notification_overdue_message' => $settings['notification_overdue_message'] ?? 'Hai un pagamento in sospeso. Ti preghiamo di regolarizzarlo.',
            'notification_pending_message' => $settings['notification_pending_message'] ?? 'Sono state generate nuove pendenze per il tuo corso.',
            'bank_transfer_info_message' => $settings['bank_transfer_info_message'] ?? "Puoi effettuare il pagamento tramite bonifico bancario.\nIBAN: IT00A0000000000000000000000\nIntestato a: Centro Yoga\nCausale: Nome Cognome - Corso",
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        Log::info('admin_settings_update_called', [
            'user_id' => optional($request->user())->id,
            'has_bank_field' => $request->has('bank_transfer_info_message'),
            'bank_len' => strlen((string) $request->input('bank_transfer_info_message')),
        ]);

        $existing = Setting::query()
            ->whereIn('key', ['membership_expiry_mode', 'membership_academic_start_date', 'membership_academic_end_date'])
            ->pluck('value', 'key');

        $data = $request->validate([
            'membership_fee' => ['required', 'numeric', 'min:0'],
            'membership_auto_generate' => ['nullable', 'boolean'],
            'membership_morosita_page_size' => ['required', 'integer', 'min:1', 'max:50'],
            'membership_expiry_mode' => ['required', Rule::in(['academic', 'rolling'])],
            'membership_academic_start_date' => ['nullable', 'date', 'required_if:membership_expiry_mode,academic'],
            'membership_academic_end_date' => ['nullable', 'date', 'required_if:membership_expiry_mode,academic', 'after:membership_academic_start_date'],
            'receipt_owner_password' => ['nullable', 'string', 'max:255'],
            'receipt_user_password_mode' => ['required', Rule::in(['blank', 'email', 'custom'])],
            'receipt_user_password_custom' => ['nullable', 'string', 'max:255', 'required_if:receipt_user_password_mode,custom'],
            'course_payment_auto_generate' => ['nullable', 'boolean'],
            'course_payment_lead_days' => ['required', 'integer', 'min:1', 'max:120'],
            'extra_day_enabled' => ['nullable', 'boolean'],
            'private_lessons_enabled' => ['nullable', 'boolean'],
            'notification_overdue_days' => ['required', 'integer', 'min:1', 'max:60'],
            'notification_overdue_message' => ['required', 'string', 'max:2000'],
            'notification_pending_message' => ['required', 'string', 'max:2000'],
            'bank_transfer_info_message' => ['required', 'string', 'max:3000'],
        ], [
            'receipt_user_password_custom.required_if' => 'Inserisci la password personalizzata quando scegli la modalità "Password personalizzata".',
        ]);

        $settingsToPersist = [
            'membership_fee' => (string) $data['membership_fee'],
            'membership_auto_generate' => $request->boolean('membership_auto_generate') ? '1' : '0',
            'membership_morosita_page_size' => (string) $data['membership_morosita_page_size'],
            'membership_expiry_mode' => $data['membership_expiry_mode'],
            'membership_academic_start_date' => $data['membership_academic_start_date'] ?? '',
            'membership_academic_end_date' => $data['membership_academic_end_date'] ?? '',
            'receipt_owner_password' => trim((string) ($data['receipt_owner_password'] ?? '')),
            'receipt_user_password_mode' => $data['receipt_user_password_mode'],
            'receipt_user_password_custom' => $data['receipt_user_password_mode'] === 'custom'
                ? trim((string) $data['receipt_user_password_custom'])
                : '',
            'course_payment_auto_generate' => $request->boolean('course_payment_auto_generate') ? '1' : '0',
            'course_payment_lead_days' => (string) $data['course_payment_lead_days'],
            'extra_day_enabled' => $request->boolean('extra_day_enabled') ? '1' : '0',
            'private_lessons_enabled' => $request->boolean('private_lessons_enabled') ? '1' : '0',
            'notification_overdue_days' => (string) $data['notification_overdue_days'],
            'notification_overdue_message' => $data['notification_overdue_message'],
            'notification_pending_message' => $data['notification_pending_message'],
            'bank_transfer_info_message' => (string) $request->input('bank_transfer_info_message', ''),
        ];

        foreach ($settingsToPersist as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $modeBefore = $existing['membership_expiry_mode'] ?? 'academic';
        $startBefore = $existing['membership_academic_start_date'] ?? '';
        $endBefore = $existing['membership_academic_end_date'] ?? '';
        $modeAfter = $settingsToPersist['membership_expiry_mode'];
        $startAfter = $settingsToPersist['membership_academic_start_date'] ?? '';
        $endAfter = $settingsToPersist['membership_academic_end_date'] ?? '';

        if (
            $modeBefore !== $modeAfter
            || ($modeAfter === 'academic' && ($startBefore !== $startAfter || $endBefore !== $endAfter))
        ) {
            $this->refreshMembershipExpirations();
        }

        Log::info('bank_transfer_info_message_saved', [
            'length' => strlen((string) $request->input('bank_transfer_info_message', '')),
            'exists' => DB::table('settings')->where('key', 'bank_transfer_info_message')->exists(),
            'db_value' => DB::table('settings')->where('key', 'bank_transfer_info_message')->value('value'),
            'db' => DB::connection()->getDatabaseName(),
        ]);

        return back()->with('status', 'Impostazioni aggiornate con successo.');
    }

    private function refreshMembershipExpirations(): void
    {
        $manager = app(MembershipManager::class);
        $mode = $manager->currentMode();

        if ($mode === 'academic') {
            $season = $manager->determineCurrentSeason();

            $updates = [
                'season_start_year' => $season['start_year'],
                'starts_at' => $season['starts_at'],
                'ends_at' => $season['ends_at'],
                'due_date' => $season['due_date'],
            ];

            MembershipSubscription::query()
                ->whereIn('status', ['pending', 'active'])
                ->with('payment')
                ->chunkById(100, function ($memberships) use ($updates, $season) {
                    foreach ($memberships as $membership) {
                        $membership->fill($updates);
                        if ($membership->isDirty()) {
                            $membership->save();
                        }

                        if ($payment = $membership->payment) {
                            $meta = $payment->meta ?? [];
                            $meta['season'] = $season['label'];

                            $payment->fill([
                                'due_date' => $season['due_date'],
                                'receipt_year' => $season['start_year'],
                                'meta' => $meta,
                            ]);

                            if ($payment->isDirty()) {
                                $payment->save();
                            }
                        }
                    }
                });

            return;
        }

        MembershipSubscription::query()
            ->with('payment')
            ->chunkById(100, function ($memberships) use ($manager) {
                foreach ($memberships as $membership) {
                    $baseStart = $membership->paid_at
                        ? $membership->paid_at->copy()->startOfDay()
                        : ($membership->starts_at ?? now())->copy()->startOfDay();

                    $season = $manager->calculateRollingSeason($baseStart);

                    $membership->fill([
                        'season_start_year' => $season['start_year'],
                        'starts_at' => $season['starts_at'],
                        'ends_at' => $season['ends_at'],
                        'due_date' => $season['due_date'],
                    ]);

                    if ($membership->isDirty()) {
                        $membership->save();
                    }

                    if ($payment = $membership->payment) {
                        $meta = $payment->meta ?? [];
                        $meta['season'] = $season['label'];

                        $payment->fill([
                            'due_date' => $season['due_date'],
                            'receipt_year' => $season['start_year'],
                            'meta' => $meta,
                        ]);

                        if ($payment->isDirty()) {
                            $payment->save();
                        }
                    }
                }
            });
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
