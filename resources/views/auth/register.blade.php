@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-emerald-50 py-16 px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10 items-start">
            <div class="lg:col-span-2 bg-white/70 backdrop-blur-sm border border-teal-100 shadow-xl rounded-3xl p-8 flex flex-col gap-6">
                <div class="space-y-4">
                    <img src="{{ asset('images/yoga-logo-big.jpg') }}" alt="Shanti Sadhana Logo" class="w-24 h-24 rounded-full shadow-md border border-white/70">
                    <h1 class="text-3xl font-semibold text-stone-900">Diventa parte di Shanti Sadhana</h1>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Completa il modulo per creare il tuo account cliente. Un amministratore approverà la tua iscrizione prima che tu possa prenotare lezioni.
                    </p>
                </div>
                <div class="bg-gradient-to-r from-teal-100 to-emerald-100 text-teal-800 px-5 py-4 rounded-2xl border border-teal-200">
                    <p class="text-sm font-semibold">Hai già un account?</p>
                    <p class="text-sm text-teal-700 mt-1">
                        <a href="{{ route('login') }}" class="underline decoration-teal-600 decoration-2 hover:text-teal-900">
                            Accedi dalla pagina di login.
                        </a>
                    </p>
                </div>
            </div>

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

            <div class="lg:col-span-3 bg-white shadow-xl border border-stone-200/80 rounded-3xl p-8">
                <form
                    method="POST"
                    action="{{ route('register.attempt') }}"
                    class="space-y-6"
                    x-data="{
                        country: '{{ old('telephone_country', '+39') }}'
                    }"
                >
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-sm font-medium text-stone-600">Nome</label>
                            <input
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('first_name') border-red-400 @enderror"
                                placeholder="Nome"
                            >
                            @error('first_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Cognome</label>
                            <input
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('last_name') border-red-400 @enderror"
                                placeholder="Cognome"
                            >
                            @error('last_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-stone-600">Email</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('email') border-red-400 @enderror"
                                placeholder="you@example.com"
                            >
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium text-stone-600">Telefono</label>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <select
                                    name="telephone_country"
                                    x-model="country"
                                    required
                                    class="p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('telephone_country') border-red-400 @enderror"
                                >
                                    @foreach ($phonePrefixes as $option)
                                        <option value="{{ $option['code'] }}" @selected(old('telephone_country', '+39') === $option['code'])>
                                            {{ $option['name'] }} ({{ $option['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="md:col-span-3 flex items-center gap-2">
                                    <span class="px-3 py-2 rounded-lg bg-stone-100 border border-stone-300 font-semibold text-stone-700" x-text="country"></span>
                                    <input
                                        type="tel"
                                        name="telephone"
                                        value="{{ old('telephone') }}"
                                        required
                                        class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('telephone') border-red-400 @enderror"
                                        placeholder="Numero di telefono"
                                    >
                                </div>
                            </div>
                            @error('telephone_country')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @error('telephone')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Password</label>
                            <input
                                type="password"
                                name="password"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('password') border-red-400 @enderror"
                                placeholder="Password"
                            >
                            @error('password')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Conferma password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                placeholder="Ripeti password"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Codice fiscale</label>
                            <input
                                type="text"
                                name="codice_fiscale"
                                value="{{ old('codice_fiscale') }}"
                                maxlength="16"
                                pattern="[A-Za-z0-9]{16}"
                                oninput="this.value = this.value.toUpperCase()"
                                required
                                class="w-full mt-1 p-3 border rounded-lg uppercase focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('codice_fiscale') border-red-400 @enderror"
                                placeholder="Codice fiscale"
                            >
                            @error('codice_fiscale')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Luogo di nascita</label>
                            <input
                                type="text"
                                name="luogo_nascita"
                                value="{{ old('luogo_nascita') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('luogo_nascita') border-red-400 @enderror"
                                placeholder="Città/Comune"
                            >
                            @error('luogo_nascita')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Data di nascita</label>
                            <input
                                type="date"
                                name="data_nascita"
                                value="{{ old('data_nascita') }}"
                                max="{{ now()->subYears(16)->format('Y-m-d') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('data_nascita') border-red-400 @enderror"
                            >
                            @error('data_nascita')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Città di residenza</label>
                            <input
                                type="text"
                                name="residenza_citta"
                                value="{{ old('residenza_citta') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('residenza_citta') border-red-400 @enderror"
                                placeholder="Città"
                            >
                            @error('residenza_citta')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Provincia</label>
                            <input
                                type="text"
                                name="residenza_provincia"
                                value="{{ old('residenza_provincia') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('residenza_provincia') border-red-400 @enderror"
                                placeholder="Provincia"
                            >
                            @error('residenza_provincia')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Stato</label>
                            <input
                                type="text"
                                name="residenza_stato"
                                value="{{ old('residenza_stato') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('residenza_stato') border-red-400 @enderror"
                                placeholder="Italia"
                            >
                            @error('residenza_stato')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-stone-600">Indirizzo (via)</label>
                            <input
                                type="text"
                                name="residenza_via"
                                value="{{ old('residenza_via') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('residenza_via') border-red-400 @enderror"
                                placeholder="Via / Piazza"
                            >
                            @error('residenza_via')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Numero civico</label>
                            <input
                                type="text"
                                name="residenza_numero_civico"
                                value="{{ old('residenza_numero_civico') }}"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('residenza_numero_civico') border-red-400 @enderror"
                                placeholder="Es. 12/B"
                            >
                            @error('residenza_numero_civico')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-stone-100 border border-stone-200 rounded-xl p-5 space-y-4">
                        <div>
                            <p class="text-sm text-stone-600 mb-3">
                                Prima di completare la registrazione, leggi i seguenti documenti:
                            </p>
                            <ul class="space-y-2 text-sm text-teal-700 font-semibold">
                                <li>
                                    <a
                                        href="{{ asset('Statuto associazione.pdf') }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="underline hover:text-teal-800"
                                    >
                                        Statuto dell'associazione
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ asset('SCAN-2275.pdf') }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="underline hover:text-teal-800"
                                    >
                                        Condizioni assicurative, scarico di responsabilità e trattamento dati personali
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <label class="inline-flex items-start gap-3 text-sm text-stone-600">
                            <input
                                type="checkbox"
                                name="statute_agreement"
                                value="1"
                                required
                                {{ old('statute_agreement') ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-500"
                            >
                            <span>
                                Confermo di aver letto e accettato lo Statuto e le Condizioni assicurative, lo scarico di responsabilità e il trattamento dei dati personali.
                            </span>
                        </label>
                        @error('statute_agreement')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-semibold py-3 rounded-lg hover:from-teal-700 hover:to-emerald-700 transition-colors shadow-md">
                        Completa iscrizione
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
