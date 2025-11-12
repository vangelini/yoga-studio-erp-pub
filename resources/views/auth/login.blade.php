@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-100 py-16 px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10 items-stretch">
            <div class="lg:col-span-2 bg-white/70 backdrop-blur-sm border border-teal-100 shadow-xl rounded-3xl p-8 flex flex-col justify-between gap-6">
                <div class="space-y-5">
                    <img src="{{ asset('images/yoga-logo-big.jpg') }}" alt="Shanti Sadhana Logo" class="w-24 h-24 rounded-full shadow-md border border-white/70">
                    <h1 class="text-3xl font-semibold text-stone-900 leading-tight">Bentornato al centro Shanti Sadhana</h1>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Accedi per gestire prenotazioni, iscrizioni e consultare il tuo calendario personale.
                    </p>
                </div>
                <div class="bg-gradient-to-r from-teal-100 to-emerald-100 text-teal-800 px-5 py-4 rounded-2xl border border-teal-200 shadow-inner">
                    <p class="text-sm font-semibold">Non hai un account?</p>
                    <p class="text-sm text-teal-700 mt-1">
                        <a href="{{ route('register') }}" class="underline decoration-teal-600 decoration-2 hover:text-teal-900">
                            Registrati come nuovo cliente.
                        </a>
                    </p>
                </div>
            </div>

            <div class="lg:col-span-3 bg-white shadow-xl border border-stone-200/80 rounded-3xl p-8">
                <h2 class="text-2xl font-semibold text-stone-900 mb-6">Accedi al tuo account</h2>
                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf

                    <div>
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

                    <div>
                        <label class="text-sm font-medium text-stone-600">Password</label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-stone-600">
                            <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                            Ricordami
                        </label>
                        <a href="{{ route('password.request') }}" class="text-teal-600 hover:text-teal-800">Password dimenticata?</a>
                    </div>

                    @if (config('services.recaptcha.site_key'))
                        <div class="space-y-2">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                            @error('g-recaptcha-response')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('captcha')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-semibold py-3 rounded-lg hover:from-teal-700 hover:to-emerald-700 transition-colors shadow-md">
                        Accedi
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
