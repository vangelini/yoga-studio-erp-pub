@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-100 py-16 px-4">
        <div class="max-w-2xl mx-auto card p-10 space-y-6 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-2xl font-semibold">
                    📧
                </div>
                <h1 class="text-3xl font-semibold text-stone-900">Conferma la tua email</h1>
                @if (session('status'))
                    <p class="text-sm text-emerald-600 font-semibold">{{ session('status') }}</p>
                @else
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Ti abbiamo inviato un link di verifica all'indirizzo <strong>{{ auth()->user()->email }}</strong>.<br>
                        Clicca sul link presente nell'email per completare la registrazione.
                    </p>
                @endif
            </div>

            <div class="space-y-4 text-sm text-stone-600">
                <p>
                    Se non hai ricevuto l'email entro pochi minuti, controlla anche la cartella SPAM oppure richiedi un nuovo invio.
                </p>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
                @csrf
                <button type="submit" class="btn-primary w-full justify-center text-sm">
                    Invia di nuovo il link di verifica
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-sm text-stone-500 hover:text-stone-700 underline">
                    Esci dall'account
                </button>
            </form>
        </div>
    </div>
@endsection
