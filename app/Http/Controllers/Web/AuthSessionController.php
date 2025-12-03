<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipManager;
use App\Services\RecaptchaValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthSessionController extends Controller
{
    public function __construct(
        private RecaptchaValidator $captcha,
        private MembershipManager $membershipManager
    ) {
    }

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register', [
            'membership_config' => $this->membershipConfiguration(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];

        if ($this->captcha->enabled()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $credentials = $request->validate($rules);

        $this->validateCaptcha($request);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        if ($user->status === 'disabled') {
            throw ValidationException::withMessages([
                'email' => __('Your account has been disabled. Please contact an administrator.'),
            ]);
        }

        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->route('dashboard');
    }

    public function register(Request $request): RedirectResponse
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'statute_agreement' => ['accepted'],
            'membership_agreement' => ['accepted'],
            'telephone_country' => ['required', 'string', 'max:10'],
            'telephone' => ['required', 'string', 'max:30'],
            'residenza_citta' => ['required', 'string', 'max:150'],
            'residenza_provincia' => ['required', 'string', 'max:150'],
            'residenza_stato' => ['required', 'string', 'max:150'],
            'residenza_via' => ['required', 'string', 'max:255'],
            'residenza_numero_civico' => ['required', 'string', 'max:20'],
            'codice_fiscale' => ['required', 'string', 'regex:/^[A-Z0-9]{16}$/i'],
            'luogo_nascita' => ['required', 'string', 'max:150'],
            'data_nascita' => ['required', 'date'],
        ];

        if ($this->captcha->enabled()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        $this->validateCaptcha($request);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => strtolower($data['email']),
            'password_hash' => Hash::make($data['password']),
            'role' => 'Client',
            'status' => 'pending',
            'telephone' => trim($data['telephone_country'].' '.$data['telephone']),
            'residenza_citta' => $data['residenza_citta'],
            'residenza_provincia' => $data['residenza_provincia'],
            'residenza_stato' => $data['residenza_stato'],
            'residenza_via' => $data['residenza_via'],
            'residenza_numero_civico' => $data['residenza_numero_civico'],
            'codice_fiscale' => strtoupper($data['codice_fiscale']),
            'luogo_nascita' => $data['luogo_nascita'],
            'data_nascita' => $data['data_nascita'],
        ]);

        $this->syncMembershipFor($user);

        app(\App\Services\NotificationService::class)->handleEvent('new_client', [
            'user' => $user->toArray(),
            'client' => $user->toArray(),
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('verification.notice')
            ->with('status', 'Ti abbiamo inviato un link di conferma via email. Clicca sul link per completare la registrazione.');
    }

    protected function syncMembershipFor(User $user): void
    {
        $this->membershipManager->ensureCurrentMembership($user, false);
    }

    private function membershipConfiguration(): array
    {
        $settings = \App\Models\Setting::query()
            ->whereIn('key', [
                'membership_fee',
                'membership_expiry_mode',
                'membership_academic_start_date',
                'membership_academic_end_date',
            ])
            ->pluck('value', 'key');

        $fee = (float) ($settings['membership_fee'] ?? config('app.membership_fee', 20));
        $mode = $settings['membership_expiry_mode'] ?? 'academic';

        if ($mode === 'academic') {
            $season = $this->membershipManager->determineCurrentSeason();
            $startLabel = optional($season['starts_at'])->translatedFormat('d/m/Y');
            $endLabel = optional($season['ends_at'])->translatedFormat('d/m/Y');
            $duration = trim(($startLabel ?? '') . ' - ' . ($endLabel ?? ''));
            if (!$duration || $duration === '-') {
                $duration = 'Anno in corso';
            }

            return [
                'mode' => 'academic',
                'fee' => $fee,
                'duration_label' => 'Validità: ' . $duration,
                'description' => 'La quota associativa é obbligatoria per partecipare alle attivitá del centro ed include la quota dell\'assicurazione sportiva dell\'intero anno.',
            ];
        }

        return [
            'mode' => 'rolling',
            'fee' => $fee,
            'duration_label' => '12 mesi dalla data di iscrizione',
            'description' => 'La quota associativa é obbligatoria per partecipare alle attivitá del centro é ha una durata di 12 mesi a partire dalla data di iscrizione.',
        ];
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function validateCaptcha(Request $request): void
    {
        if (!$this->captcha->enabled()) {
            return;
        }

        if (!$this->captcha->verify($request->input('g-recaptcha-response'), $request->ip())) {
            throw ValidationException::withMessages([
                'captcha' => __('Verifica anti-spam non superata. Riprova.'),
            ]);
        }
    }
}
