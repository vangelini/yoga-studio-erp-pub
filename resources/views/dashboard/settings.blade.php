@extends('layouts.app')

@section('content')
@php
    $autoGenerateOld = old('membership_auto_generate', $membership_auto_generate);
    $courseAutoOld = old('course_payment_auto_generate', $course_payment_auto_generate);
    $courseLeadOld = old('course_payment_lead_days', $course_payment_lead_days);
@endphp
<div class="max-w-3xl mx-auto" x-data="{ mode: '{{ $receipt_user_password_mode }}' }">
    <form id="membership-generate-form" method="POST" action="{{ route('admin.memberships.generate') }}" class="hidden">
        @csrf
        <input type="hidden" name="send_notifications" value="0">
    </form>
    <form id="course-payments-generate-form" method="POST" action="{{ route('admin.courses.payments.generate') }}" class="hidden">
        @csrf
        <input type="hidden" name="send_notifications" value="0">
    </form>
    <div class="card p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Impostazioni amministratore</h1>
            <p class="text-sm text-stone-500">Configura le impostazioni generali del centro.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Quota annuale (Euro)</label>
                <input type="number" step="0.01" name="membership_fee" value="{{ old('membership_fee', $membership_fee) }}" required class="input-field mt-1">
                @error('membership_fee')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" name="membership_auto_generate" value="1" id="auto-generate" {{ $autoGenerateOld ? 'checked' : '' }} class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                    <label for="auto-generate" class="text-sm text-stone-600">Genera automaticamente le pendenze delle quote associative quando un amministratore accede al pannello.</label>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition" onclick="if (confirm('Generare subito le pendenze delle quote associative?')) { const form = document.getElementById('membership-generate-form'); form.querySelector('[name=send_notifications]').value = confirm('Inviare una notifica agli utenti morosi?') ? '1' : '0'; form.submit(); }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Genera quote ora
                    </button>
                    <form method="POST" action="{{ route('notifications.pending.resend') }}" onsubmit="return confirm('Reinviare le notifiche delle pendenze generate per i corsi?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-stone-100 transition">
                            Reinvia notifica pendenze
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-3 border border-stone-200 rounded-xl bg-stone-50 px-4 py-4">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="course_payment_auto_generate" value="1" id="course-auto" {{ $courseAutoOld ? 'checked' : '' }} class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                        <label for="course-auto" class="text-sm text-stone-600">Abilita la generazione automatica delle pendenze per i corsi in abbonamento.</label>
                    </div>
                    <div>
                        <label class="text-xs uppercase font-semibold text-stone-500">Giorni di anticipo</label>
                        <input type="number" min="1" max="120" name="course_payment_lead_days" value="{{ $courseLeadOld }}" class="input-field text-sm mt-1 w-32">
                        <p class="text-xs text-stone-500 mt-1">La pendenza del periodo successivo verrà creata questo numero di giorni prima della scadenza dell'abbonamento.</p>
                        @error('course_payment_lead_days')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition" onclick="if (confirm('Generare subito le pendenze per i corsi?')) { const form = document.getElementById('course-payments-generate-form'); form.querySelector('[name=send_notifications]').value = confirm('Inviare subito la notifica delle nuove pendenze?') ? '1' : '0'; form.submit(); }">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2h-5.586a1 1 0 01-.707-.293l-1.414-1.414A2 2 0 009.586 3H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Genera pendenze corsi
                        </button>
                        @if(!empty($course_payment_last_run))
                            <span class="text-[11px] text-stone-500">
                                Ultima esecuzione: {{ \Carbon\Carbon::parse($course_payment_last_run['run_at'])->format('d/m/Y H:i') ?? '—' }}
                                · nuove pendenze: {{ $course_payment_last_run['created'] ?? 0 }}
                                @if(!empty($course_payment_last_run['manual']))
                                    · esecuzione manuale
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-2 border border-amber-200 rounded-xl bg-amber-50 px-4 py-4">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="private_lessons_enabled" value="1" id="private-lessons"
                        {{ old('private_lessons_enabled', $private_lessons_enabled) ? 'checked' : '' }}
                        class="mt-1 rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                    <label for="private-lessons" class="text-sm text-stone-700">
                        Abilita la gestione delle lezioni individuali. Quando attivo i clienti possono prenotare lezioni private,
                        gli insegnanti possono gestire disponibilità e prossime lezioni e l'amministratore visualizza il flag
                        "Può tenere lezioni private" nella gestione docenti.
                    </label>
                </div>
                <p class="text-xs text-stone-500">
                    Disattivando l'opzione tutte le sezioni relative alle lezioni individuali scompaiono per clienti, insegnanti e amministratori.
                </p>
            </div>

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Morosità quote per pagina</label>
                <input type="number" min="1" max="50" name="membership_morosita_page_size" value="{{ old('membership_morosita_page_size', $membership_morosita_page_size) }}" required class="input-field mt-1">
                <p class="text-xs text-stone-500">Numero di elementi mostrati per pagina nel pannello “Morosità quota associativa”.</p>
                @error('membership_morosita_page_size')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" name="extra_day_enabled" value="1" id="extra-day" {{ old('extra_day_enabled', $extra_day_enabled) ? 'checked' : '' }} class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                    <label for="extra-day" class="text-sm text-stone-600">Abilita la modalità “Un giorno in più” (lezione extra da corso candidato).</label>
                </div>
                <p class="text-xs text-stone-500">Quando attivo, gli Allievi possono aggiungere una lezione settimanale extra scegliendo tra i corsi candidati. Il costo della lezione extra viene aggiunto al prezzo base e proratato sulle lezioni rimanenti.</p>
            </div>

            <div class="space-y-3 border border-stone-200 rounded-xl bg-white px-4 py-4">
                <p class="text-sm font-semibold text-stone-700">Notifiche automatiche</p>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="text-xs uppercase font-semibold text-stone-500">Giorni morosità</label>
                        <input type="number" min="1" max="60" name="notification_overdue_days" value="{{ old('notification_overdue_days', $notification_overdue_days) }}" class="input-field mt-1 w-32">
                        <p class="text-xs text-stone-500 mt-1">Numero di giorni trascorsi dalla scadenza prima di inviare l'avviso di morosità.</p>
                        @error('notification_overdue_days')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs uppercase font-semibold text-stone-500">Messaggio morosità</label>
                        <textarea name="notification_overdue_message" rows="3" class="input-field mt-1">{{ old('notification_overdue_message', $notification_overdue_message) }}</textarea>
                        @error('notification_overdue_message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs uppercase font-semibold text-stone-500">Messaggio pendenze generate</label>
                        <textarea name="notification_pending_message" rows="3" class="input-field mt-1">{{ old('notification_pending_message', $notification_pending_message) }}</textarea>
                        @error('notification_pending_message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <label class="text-xs uppercase font-semibold text-stone-500">Password proprietario ricevute</label>
                <input type="text" name="receipt_owner_password" value="{{ old('receipt_owner_password', $receipt_owner_password) }}" class="input-field" placeholder="Lascia vuoto per nessuna protezione" autocomplete="off">
                <p class="text-xs text-stone-500">Protegge la ricevuta da modifiche non autorizzate. Lasciala vuota per disabilitare la protezione.</p>
                @error('receipt_owner_password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <label class="text-xs uppercase font-semibold text-stone-500">Modalità password destinatario</label>
                <div class="space-y-2">
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="blank" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Nessuna password
                            <span class="block text-xs text-stone-500">L'allieva/o potrà aprire la ricevuta senza password.</span>
                        </span>
                    </label>
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="email" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Password = email allieva/o
                            <span class="block text-xs text-stone-500">Usa l'indirizzo email dell' allieva/o come password.</span>
                        </span>
                    </label>
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="custom" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Password personalizzata
                            <span class="block text-xs text-stone-500">Tutte le ricevute useranno la password indicata qui sotto.</span>
                        </span>
                    </label>
                </div>
                <div x-show="mode === 'custom'" x-cloak class="space-y-2">
                    <input type="text" name="receipt_user_password_custom" value="{{ old('receipt_user_password_custom', $receipt_user_password_custom) }}" class="input-field" placeholder="Inserisci la password condivisa" autocomplete="off">
                    @error('receipt_user_password_custom')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
                    </svg>
                    Torna al dashboard
                </a>
                <button type="submit" class="btn-primary">Salva impostazioni</button>
            </div>
        </form>
    </div>
</div>
@endsection
