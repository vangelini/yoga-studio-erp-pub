@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="card p-6 space-y-4">
        <h2 class="text-2xl font-semibold text-stone-900">Invio WhatsApp manuale</h2>
        <p class="text-sm text-stone-500">Sono stati generati {{ count($links) }} link WhatsApp per la notifica <strong>{{ $notification->title }}</strong>. Verranno aperti automaticamente in nuove schede (se il browser lo consente) e puoi anche cliccarli manualmente dall'elenco qui sotto.</p>

        @if(count($links))
            <button
                type="button"
                id="open-all-whatsapp"
                class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-3 py-2 text-xs font-semibold text-teal-600 hover:bg-teal-50 transition"
            >
                Apri tutte le chat WhatsApp
            </button>
        @endif

        <div id="whatsapp-links" class="space-y-3">
            @forelse ($links as $link)
                <div class="flex flex-col gap-1 rounded-xl border border-stone-200 bg-white px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-teal-700">{{ $link['user']->name }}</span>
                        <span class="text-xs text-stone-400">{{ $link['user']->telephone }}</span>
                    </div>
                    <p class="text-sm text-stone-600">{{ $link['message'] }}</p>
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-800">
                        Apri chat
                    </a>
                </div>
            @empty
                <p class="text-sm text-stone-500">Nessun destinatario con numero di telefono valido.</p>
            @endforelse
        </div>

        <a href="{{ route('notifications.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Torna al centro notifiche</a>
    </div>
</div>
@endsection

@push('scripts')
@if(count($links))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urls = @json(array_column($links, 'url'));
        let triggered = false;
        const openLinks = () => {
            urls.forEach(url => window.open(url, '_blank'));
        };

        const trigger = document.getElementById('open-all-whatsapp');
        if (trigger) {
            trigger.addEventListener('click', () => {
                if (!triggered) {
                    triggered = true;
                    trigger.classList.add('opacity-50');
                    trigger.setAttribute('disabled', 'disabled');
                }
                openLinks();
            });
        }
    });
</script>
@endif
@endpush
