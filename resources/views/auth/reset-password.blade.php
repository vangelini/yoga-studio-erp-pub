@extends('layouts.base')

@section('body')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stone-100 via-white to-stone-200 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-stone-800">Reimposta la password</h2>
            <p class="mt-2 text-sm text-stone-500">
                Inserisci una nuova password sicura per il tuo account.
            </p>
        </div>
        <div class="bg-white shadow-xl border border-stone-200/70 rounded-2xl p-6 space-y-6">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-xs uppercase font-semibold text-stone-500 tracking-wide">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus class="input-field mt-1">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase font-semibold text-stone-500 tracking-wide">Nuova password</label>
                    <input type="password" name="password" required class="input-field mt-1" autocomplete="new-password">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase font-semibold text-stone-500 tracking-wide">Conferma password</label>
                    <input type="password" name="password_confirmation" required class="input-field mt-1" autocomplete="new-password">
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition">
                    Aggiorna password
                </button>
            </form>
        </div>
        <div class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-teal-600 font-semibold hover:text-teal-700">Torna al login</a>
        </div>
    </div>
</div>
@endsection
