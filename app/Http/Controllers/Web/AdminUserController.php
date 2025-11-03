<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use App\Services\MembershipManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['Admin', 'Teacher', 'Client'])],
            'telephone_country' => ['required', 'string', 'max:10'],
            'telephone' => ['required', 'string', 'max:30'],
            'residenza_citta' => ['nullable', 'string', 'max:150'],
            'residenza_provincia' => ['nullable', 'string', 'max:150'],
            'residenza_stato' => ['nullable', 'string', 'max:150'],
            'residenza_via' => ['nullable', 'string', 'max:255'],
            'residenza_numero_civico' => ['nullable', 'string', 'max:20'],
            'codice_fiscale' => ['nullable', 'string', 'regex:/^[A-Z0-9]{16}$/i'],
            'luogo_nascita' => ['nullable', 'string', 'max:150'],
            'data_nascita' => ['nullable', 'date'],
            'can_host_private' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => strtolower($data['email']),
                'password_hash' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => $data['role'] === 'Client' ? 'pending' : 'active',
                'email_verified_at' => $data['role'] === 'Client' ? null : now(),
                'telephone' => trim($data['telephone_country'].' '.$data['telephone']),
                'residenza_citta' => $data['residenza_citta'],
                'residenza_provincia' => $data['residenza_provincia'],
                'residenza_stato' => $data['residenza_stato'],
                'residenza_via' => $data['residenza_via'],
                'residenza_numero_civico' => $data['residenza_numero_civico'],
                'codice_fiscale' => strtoupper($data['codice_fiscale'] ?? ''),
                'luogo_nascita' => $data['luogo_nascita'],
                'data_nascita' => $data['data_nascita'],
            ]);

            if ($data['role'] === 'Teacher') {
                Teacher::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'profile_picture_url' => $user->teacher?->profile_picture_url ?? "https://picsum.photos/seed/{$user->id}/100/100",
                        'bio' => $user->teacher?->bio ?? 'Benvenuto! Aggiorna la tua biografia.',
                        'specializations' => $user->teacher?->specializations ?? [],
                        'can_host_private' => (bool) ($data['can_host_private'] ?? false),
                    ]
                );
            }

            if ($data['role'] === 'Client') {
                app(MembershipManager::class)->ensureCurrentMembership($user, false);
                $user->sendEmailVerificationNotification();
            }
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'Utente creato con successo.');
    }

    public function updateRoleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'role' => ['required', Rule::in(['Admin', 'Teacher', 'Client'])],
            'status' => ['required', Rule::in(['active', 'pending', 'disabled'])],
        ]);

        DB::transaction(function () use ($user, $data) {
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
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'User settings updated.');
    }

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
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
        ]);

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => strtolower($data['email']),
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

        app(MembershipManager::class)->ensureCurrentMembership($user, false);

        return redirect()
            ->route('dashboard')
            ->with('status', "Dati aggiornati per {$user->email}.");
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $status = Password::sendResetLink(['email' => $user->email]);

        return redirect()
            ->route('dashboard')
            ->with('status', __($status));
    }

    public function resendVerification(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('status', 'La email del membro è già verificata.');
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('dashboard')->with('status', 'Email di verifica reinviata con successo.');
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $user->update(['status' => 'active']);

        return redirect()->route('dashboard')->with('status', 'Account attivato.');
    }

    public function togglePrivateClasses(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorizeAdmin();

        $teacher->update([
            'can_host_private' => $request->boolean('can_host_private'),
        ]);

        return redirect()->route('dashboard')->with('status', 'Impostazioni lezioni private aggiornate.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $filename = 'utenti_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $users = User::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // Add BOM so Excel recognises UTF-8
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nome',
                'Cognome',
                'Email',
                'Ruolo',
                'Stato',
                'Telefono',
                'Città',
                'Provincia',
                'Stato',
                'Via',
                'Numero civico',
                'Codice fiscale',
                'Luogo di nascita',
                'Data di nascita',
                'Email verificata',
                'Creato il',
            ]);

            foreach ($users as $user) {
                $birthDate = $user->data_nascita ? Carbon::parse($user->data_nascita)->format('d/m/Y') : '';
                $emailVerified = $user->email_verified_at ? Carbon::parse($user->email_verified_at)->format('d/m/Y H:i') : 'No';
                $createdAt = $user->created_at ? $user->created_at->format('d/m/Y H:i') : '';

                fputcsv($handle, [
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->role,
                    ucfirst($user->status ?? ''),
                    $user->telephone,
                    $user->residenza_citta,
                    $user->residenza_provincia,
                    $user->residenza_stato,
                    $user->residenza_via,
                    $user->residenza_numero_civico,
                    $user->codice_fiscale,
                    $user->luogo_nascita,
                    $birthDate,
                    $emailVerified,
                    $createdAt,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updateTeacherCourses(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'course_ids' => ['sometimes', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = collect($data['course_ids'] ?? [])->map(fn ($id) => (int) $id)->all();

        Course::where('teacher_id', $teacher->user_id)
            ->whereNotIn('id', $courseIds)
            ->update(['teacher_id' => null]);

        if (!empty($courseIds)) {
            Course::whereIn('id', $courseIds)->update(['teacher_id' => $teacher->user_id]);
        }

        return redirect()->route('dashboard')->with('status', 'Corsi assegnati al docente.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
    }
}
