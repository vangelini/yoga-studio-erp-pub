@extends('layouts.app')

@section('content')
@php
    $phonePrefixes = [
        ['code' => '+39', 'name' => 'Italia'],
        ['code' => '+33', 'name' => 'Francia'],
        ['code' => '+49', 'name' => 'Germania'],
        ['code' => '+34', 'name' => 'Spagna'],
        ['code' => '+44', 'name' => 'Regno Unito'],
        ['code' => '+1', 'name' => 'Stati Uniti'],
    ];
@endphp

<section
    x-data="{
        showCreateTeacher: false,
        expandedTeacher: null,
        toggleTeacher(id) {
            this.expandedTeacher = this.expandedTeacher === id ? null : id;
        }
    }"
    class="space-y-10"
>
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-stone-900">Insegnanti del centro</h3>
                <p class="text-sm text-stone-500">Gestisci anagrafica degli insegnanti.</p>
            </div>
            <button type="button" class="btn-primary text-xs self-start md:self-auto" @click="showCreateTeacher = !showCreateTeacher">
                <span class="text-sm font-semibold" x-text="showCreateTeacher ? 'Nascondi ' : 'Nuovo Insegnante'"></span>
            </button>
        </div>

        <form
            x-show="showCreateTeacher"
            x-transition
            method="POST"
            action="{{ route('admin.users.store') }}"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6 mb-4"
        >
            @csrf
            <input type="hidden" name="role" value="Teacher">
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                <input type="text" name="first_name" required class="input-field text-sm" placeholder="Nome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" required class="input-field text-sm" placeholder="Cognome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" required class="input-field text-sm" placeholder="insegnante@example.com">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Password temporanea</label>
                <input type="password" name="password" minlength="6" required class="input-field text-sm" placeholder="••••••">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                <select name="telephone_country" class="input-field text-sm">
                    @foreach ($phonePrefixes as $option)
                        <option value="{{ $option['code'] }}" @selected($option['code'] === '+39')>{{ $option['name'] }} ({{ $option['code'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                <input type="text" name="telephone" required class="input-field text-sm" placeholder="000 000 0000">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                <input type="text" name="residenza_citta" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                <input type="text" name="residenza_provincia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                <input type="text" name="residenza_stato" value="Italia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                <input type="text" name="residenza_via" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                <input type="text" name="residenza_numero_civico" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                <input type="text" name="codice_fiscale" required class="input-field text-sm uppercase">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2 flex items-center gap-2">
                <input id="teacher-private" type="checkbox" name="can_host_private" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                <label for="teacher-private" class="text-sm text-stone-600">Abilita immediatamente le lezioni private</label>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Registra insegnante</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Nome</th>
                        <th class="px-4 py-3 text-left font-semibold">Email</th>
                        <th class="px-4 py-3 text-left font-semibold">Telefono</th>
                        <th class="px-4 py-3 text-left font-semibold">Stato</th>
                        <th class="px-4 py-3 text-left font-semibold">Lezioni private</th>
                        <th class="px-4 py-3 text-left font-semibold">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($teacherAdminList as $teacher)
                        @php
                            $teacherUser = $teacher->user;
                            $teacherPhoneParts = explode(' ', $teacherUser->telephone ?? '', 2);
                            $teacherPrefix = $teacherPhoneParts[0] ?? '+39';
                            $teacherNumber = $teacherPhoneParts[1] ?? '';
                            $teacherWhatsapp = preg_replace('/\D+/', '', $teacherUser->telephone ?? '');
                            $teacherCourseTitles = $teacher->courses->pluck('title')->filter()->values();
                        @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800">{{ $teacherUser->name }}</td>
                            <td class="px-4 py-3 text-stone-600">
                                @if ($teacherUser->email)
                                    <a href="mailto:{{ $teacherUser->email }}" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">{{ $teacherUser->email }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                @if ($teacherUser->telephone && $teacherWhatsapp)
                                    <a href="https://wa.me/{{ $teacherWhatsapp }}" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        {{ $teacherUser->telephone }}
                                    </a>
                                @else
                                    {{ $teacherUser->telephone ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($teacherUser->status === 'active') bg-emerald-100 text-emerald-700
                                    @elseif($teacherUser->status === 'pending') bg-amber-100 text-amber-700
                                    @else bg-rose-100 text-rose-700 @endif">
                                    {{ ucfirst($teacherUser->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                {!! $teacher->can_host_private
                                    ? '<span class="text-emerald-600 font-semibold">Abilitate</span>'
                                    : '<span class="text-stone-500">Disabilitate</span>' !!}
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleTeacher({{ $teacher->id }})">
                                    <span x-text="expandedTeacher === {{ $teacher->id }} ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedTeacher === {{ $teacher->id }}" x-cloak x-transition>
                            <td colspan="6" class="px-4 pb-5">
                                <div class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
                                        <form method="POST" action="{{ route('admin.users.profile', $teacherUser) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                                                <input type="text" name="first_name" value="{{ $teacherUser->first_name }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                                                <input type="text" name="last_name" value="{{ $teacherUser->last_name }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                                                <input type="email" name="email" value="{{ $teacherUser->email }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                                                <select name="telephone_country" class="input-field text-sm">
                                                    @foreach ($phonePrefixes as $option)
                                                        <option value="{{ $option['code'] }}" @selected($teacherPrefix === $option['code'])>{{ $option['name'] }} ({{ $option['code'] }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                                                <input type="text" name="telephone" value="{{ $teacherNumber }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                                                <input type="text" name="residenza_citta" value="{{ $teacherUser->residenza_citta }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                                                <input type="text" name="residenza_provincia" value="{{ $teacherUser->residenza_provincia }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                                                <input type="text" name="residenza_stato" value="{{ $teacherUser->residenza_stato }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                                                <input type="text" name="residenza_via" value="{{ $teacherUser->residenza_via }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                                                <input type="text" name="residenza_numero_civico" value="{{ $teacherUser->residenza_numero_civico }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                                                <input type="text" name="codice_fiscale" value="{{ $teacherUser->codice_fiscale }}" required class="input-field text-sm uppercase">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                                                <input type="text" name="luogo_nascita" value="{{ $teacherUser->luogo_nascita }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                                                <input type="date" name="data_nascita" value="{{ optional($teacherUser->data_nascita)->format('Y-m-d') }}" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Stato utente</label>
                                                <select name="status" class="input-field text-sm">
                                                    @foreach (['active' => 'Attivo', 'pending' => 'In attesa', 'disabled' => 'Disabilitato'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(($teacherUser->status ?? '') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($teacherUser->role === 'Teacher')
                                                <div class="md:col-span-2 flex items-center gap-2">
                                                    <input type="hidden" name="teacher_can_host_private" value="0">
                                                    <input type="checkbox" name="teacher_can_host_private" value="1" @checked($teacher->can_host_private) class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                    <label class="text-sm text-stone-600">Può tenere lezioni private</label>
                                                </div>
                                            @endif
                                            <div class="md:col-span-2 flex justify-end gap-2">
                                                <button type="reset" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">Cancella</button>
                                                <button type="submit" class="btn-primary text-sm">Salva dati</button>
                                            </div>
                                        </form>
                                        <div class="lg:col-span-4 space-y-2">
                                            <p class="text-xs uppercase text-stone-500 font-semibold">Corsi assegnati</p>
                                            <div class="flex flex-wrap gap-2 text-sm">
                                                @if($teacherCourseTitles->isNotEmpty())
                                                    @foreach ($teacherCourseTitles as $title)
                                                        <span class="inline-flex items-center rounded-full bg-teal-50 border border-teal-100 px-3 py-1 text-teal-700 font-semibold">{{ $title }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-stone-500">Nessun corso assegnato</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <form method="POST" action="{{ route('admin.users.passwordEmail', $teacherUser) }}" class="flex items-center gap-3">
                                                @csrf
                                                <button type="submit" class="text-xs bg-rose-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-rose-600 transition-colors">Invia reset password</button>
                                            </form>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-stone-600">
                                                <span class="font-semibold text-stone-700 uppercase tracking-wide">Verifica Email:</span>
                                                @if($teacherUser->email_verified_at)
                                                    <span class="text-emerald-600 font-semibold">Sì ({{ optional($teacherUser->email_verified_at)->format('d/m/Y H:i') }})</span>
                                                @else
                                                    <span class="text-amber-600 font-semibold">No</span>
                                                    <form method="POST" action="{{ route('admin.users.resendVerification', $teacherUser) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs bg-amber-500 text-white font-semibold px-3 py-2 rounded-lg hover:bg-amber-600 transition-colors">Reinvia email</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">Nessun insegnante registrato al momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-start">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
            </svg>
            Torna al dashboard
        </a>
    </div>
</section>
@endsection
