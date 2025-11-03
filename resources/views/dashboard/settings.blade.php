@extends('layouts.app')

@section('content')
@php
    $autoGenerateOld = old('membership_auto_generate', $membership_auto_generate);
@endphp
<div class="max-w-3xl mx-auto" x-data="{ mode: '{{ $receipt_user_password_mode }}' }">
    <div class="card p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Impostazioni amministratore</h1>
            <p class="text-sm text-stone-500">Configura le impostazioni generali del centro.</p>
        </div>

        @if(session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-700">
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

            <div class="flex items-center gap-2">
                <input type="checkbox" name="membership_auto_generate" value="1" id="auto-generate" {{ $autoGenerateOld ? 'checked' : '' }} class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                <label for="auto-generate" class="text-sm text-stone-600">Genera automaticamente le pendenze delle quote quando si accede al pannello admin</label>
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
                            <span class="block text-xs text-stone-500">Il cliente potrà aprire la ricevuta senza password.</span>
                        </span>
                    </label>
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="email" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Password = email cliente
                            <span class="block text-xs text-stone-500">Usa l'indirizzo email del cliente come password.</span>
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
