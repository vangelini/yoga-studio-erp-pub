@extends('layouts.app')

@section('content')
<section class="space-y-6">
    @php
        $statusLabels = [
            'pending' => __('In attesa'),
            'paid' => __('Pagato'),
            'waived' => __('Annullato'),
        ];
    @endphp
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Contabilità</h1>
            <p class="text-sm text-stone-500">Elenco pagamenti con filtri per stato e data di pagamento.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.accounting.export', request()->query()) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-3 py-2 text-xs font-semibold text-teal-600 hover:bg-teal-50"
            >
                Esporta CSV
            </a>
            <form method="GET" action="{{ route('admin.accounting.exportReceipts') }}" class="flex items-center gap-2">
                <input type="number" name="year" value="{{ request('year', now()->year) }}" min="2000" max="{{ now()->year + 1 }}" class="input-field text-xs w-24" title="Anno ricevute">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                    Esporta ricevute ZIP
                </button>
            </form>
            
        </div>
    </div>

    <div x-data="{ open: false }" class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <button
            type="button"
            class="flex w-full items-center justify-between px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50"
            @click="open = !open"
        >
            <span>Filtri contabilit&aacute;</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6" />
            </svg>
        </button>
        <form
            x-show="open"
            x-cloak
            x-transition
            method="GET"
            action="{{ route('admin.accounting.index') }}"
            class="flex flex-col gap-3 px-4 py-3 text-sm md:flex-row md:items-end"
        >
            <div class="min-w-[180px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Stato</label>
                <select name="status" class="input-field text-sm">
                    <option value="">Tutti</option>
                    @foreach ($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Da data pagamento</label>
                <input type="date" name="from" value="{{ request('from') }}" class="input-field text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">A data pagamento</label>
                <input type="date" name="to" value="{{ request('to') }}" class="input-field text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Ordina per</label>
                <select name="sort" class="input-field text-sm">
                    @foreach (['paid_at' => 'Data pagamento', 'due_date' => 'Data scadenza', 'amount' => 'Importo', 'status' => 'Stato', 'type' => 'Tipo'] as $key => $label)
                        <option value="{{ $key }}" @selected(request('sort', 'paid_at') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Direzione</label>
                <select name="dir" class="input-field text-sm">
                    <option value="asc" @selected(request('dir') === 'asc')>Ascendente</option>
                    <option value="desc" @selected(request('dir', 'desc') === 'desc')>Discendente</option>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Elementi per pagina</label>
                <select name="per_page" class="input-field text-sm">
                    @foreach ([25, 50, 100] as $option)
                        <option value="{{ $option }}" @selected(request('per_page', $perPage ?? 25) == $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 md:ml-auto">
                <button type="submit" class="btn-primary text-xs">Filtra</button>
                <a href="{{ route('admin.accounting.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Pulisci</a>
                
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Allievo</th>
                    <th class="px-3 py-2 text-left">Importo</th>
                    <th class="px-3 py-2 text-left">Tipo</th>
                    <th class="px-3 py-2 text-left">Metodo</th>
                    <th class="px-3 py-2 text-left">CRO / Riferimento</th>
                    <th class="px-3 py-2 text-left">Nota pagamento</th>
                    <th class="px-3 py-2 text-left">Stato</th>
                    <th class="px-3 py-2 text-left">Data pagamento</th>
                    <th class="px-3 py-2 text-left">Scadenza</th>
                    <th class="px-3 py-2 text-left">Anno</th>
                    <th class="px-3 py-2 text-left">Operatore</th>
                    <th class="px-3 py-2 text-left">Ricevuta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-stone-50">
                        <td class="px-3 py-2">{{ $payment->id }}</td>
                        <td class="px-3 py-2">
                            <div class="flex flex-col">
                                <span class="font-semibold text-stone-800">{{ $payment->user?->name ?? '—' }}</span>
                                <span class="text-xs text-stone-500">{{ $payment->user?->email }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2 font-semibold text-stone-800">€ {{ number_format($payment->amount ?? 0, 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ $payment->type }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ $payment->method ?? '—' }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ $payment->meta['transfer_reference'] ?? '—' }}</td>
                        <td class="px-3 py-2 text-stone-600">
                            {{ $payment->meta['manual_note'] ?? $payment->status_reason ?? '—' }}
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold
                                @if($payment->status === 'paid') bg-emerald-100 text-emerald-700
                                @elseif($payment->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-rose-100 text-rose-700 @endif">
                                {{ $statusLabels[$payment->status] ?? ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-stone-600">{{ optional($payment->paid_at)->format('d/m/Y') ?: '—' }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ optional($payment->due_date)->format('d/m/Y') ?: '—' }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ $payment->receipt_year ?? '—' }}</td>
                        <td class="px-3 py-2 text-stone-600">{{ $payment->processedBy?->name ?? '—' }}</td>
                        <td class="px-3 py-2">
                            @if ($payment->receipt_path)
                                <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted text-xs">
                                    Apri ricevuta
                                </a>
                            @else
                                <span class="text-[11px] text-stone-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-4 text-center text-stone-500">Nessun pagamento trovato.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-xs text-stone-500">
            <form method="GET" action="{{ route('admin.accounting.index') }}" class="inline">
                Mostra
                @foreach(request()->except('page', 'per_page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                    <select name="per_page" class="input-field text-xs inline-block w-auto align-middle" onchange="this.form.submit()">
                    @foreach ([25, 50, 100] as $option)
                        <option value="{{ $option }}" @selected($payments->perPage() == $option)>{{ $option }}</option>
                    @endforeach
                </select> righe per pagina.
                </form>
            </div>
        <div>
            {{ $payments->links() }}
        </div>
    </div>

</section>
@endsection
