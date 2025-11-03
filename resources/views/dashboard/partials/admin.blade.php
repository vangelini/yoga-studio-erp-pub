@php
    $phonePrefixes = [
        ['code' => '+39', 'name' => 'Italia'],
        ['code' => '+33', 'name' => 'Francia'],
        ['code' => '+49', 'name' => 'Germania'],
        ['code' => '+34', 'name' => 'Spagna'],
        ['code' => '+44', 'name' => 'Regno Unito'],
        ['code' => '+1', 'name' => 'Stati Uniti'],
    ];

    $clientCount = $clients->count();
    $teacherCount = $teacherAdminList->count();
    $courseCount = $courses->count();

    $teacherSelectOptions = $teacherAdminList
        ->mapWithKeys(fn ($teacher) => [$teacher->user_id => $teacher->user->name])
        ->sort();

    $dayOptions = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

    $documentDefinitions = [
        'id_front' => "CI - fronte",
        'id_back' => "CI - retro",
        'health_card' => 'Tessera sanitaria',
        'medical_certificate' => 'Certificato medico',
    ];
@endphp
<section
    x-data="{
        showCreateClient: false,
        showCreateTeacher: false,
        expandedClient: null,
        expandedTeacher: null,
        toggleClient(id) {
            this.expandedClient = this.expandedClient === id ? null : id;
        },
        toggleTeacher(id) {
            this.expandedTeacher = this.expandedTeacher === id ? null : id;
        },
        showGenerateModal: false,
        membershipPanelOpen: @json(request()->has('membership_page')),
    }"
    class="space-y-12"
>
    <div class="relative overflow-hidden rounded-2xl border border-teal-200/40 bg-gradient-to-r from-teal-600 via-teal-500 to-emerald-500 text-white shadow-lg">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/honeycomb.png')] opacity-20 pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Pannello amministrazione</p>
                <h2 class="text-3xl md:text-4xl font-semibold">Gestisci associati, docenti e corsi</h2>
                <p class="text-white/85 leading-relaxed">
                    Verifica i dati degli iscritti, assegna corsi ai docenti e monitora pagamenti e quote associative in un unico posto.
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.settings.edit') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 3.75a1.5 1.5 0 013 0V5a1.5 1.5 0 01-3 0V3.75zM5.636 5.636a1.5 1.5 0 010 2.121l-.884.884a1.5 1.5 0 01-2.122-2.121l.884-.884a1.5 1.5 0 012.122 0zM3.75 10.5H5a1.5 1.5 0 010 3H3.75a1.5 1.5 0 010-3zM5.636 18.364a1.5 1.5 0 01-2.122 0l-.884-.884a1.5 1.5 0 112.122-2.121l.884.884a1.5 1.5 0 000 2.121zM10.5 18.75V20a1.5 1.5 0 003 0v-1.25a1.5 1.5 0 00-3 0zM18.364 18.364a1.5 1.5 0 002.122 0l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 000 2.121zM20.25 13.5H19a1.5 1.5 0 110-3h1.25a1.5 1.5 0 110 3zM18.364 5.636l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 002.122 2.121z"/>
                        </svg>
                        Impostazioni
                    </a>
                    <form method="POST" action="{{ route('admin.memberships.generate') }}" x-ref="generateMembershipForm">
                        @csrf
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition" @click="showGenerateModal = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m6-6H6" />
                            </svg>
                            Genera quote
                        </button>
                    </form>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/30 shadow-inner text-center text-xs uppercase tracking-widest">
                <div class="flex flex-col text-white/80">
                    <span>Clienti</span>
                    <span class="text-2xl font-semibold text-white">{{ $clientCount }}</span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Corsi</span>
                    <span class="text-2xl font-semibold text-white">{{ $courseCount }}</span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Docenti</span>
                    <span class="text-2xl font-semibold text-white">{{ $teacherCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="showGenerateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @keydown.escape.window="showGenerateModal = false"
    >
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-stone-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-stone-900">Genera quote associative</h3>
            </div>
            <div class="space-y-4 px-6 py-5 text-sm text-stone-600">
                <p>Questa operazione verifica tutti i clienti e crea le quote annuali mancanti per la stagione corrente. Le pendenze generate resteranno in stato <strong>pending</strong> finché non verranno saldate manualmente.</p>
                <p class="text-xs text-stone-500">Usa questa funzione all’inizio della stagione o quando aggiungi nuovi clienti che non hanno ancora una quota associativa attiva.</p>
            </div>
            <div class="flex flex-col gap-2 border-t border-stone-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                    @click="showGenerateModal = false"
                >
                    Annulla
                </button>
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition"
                    @click="$refs.generateMembershipForm.submit(); showGenerateModal = false;"
                >
                    Conferma operazione
                </button>
            </div>
        </div>
    </div>

    @if(isset($membershipSummary))
        @php
            $membershipCurrentPage = $membershipSummary['current_page'] ?? 1;
            $membershipLastPage = $membershipSummary['last_page'] ?? 1;
        @endphp
        <div class="card p-3 space-y-2">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-left transition hover:border-stone-300 hover:bg-stone-100"
                @click="membershipPanelOpen = !membershipPanelOpen"
            >
                <div>
                    <h3 class="text-lg font-semibold text-stone-900">Morosità quota associativa</h3>
                    <p class="text-xs text-stone-500">Totale quote in attesa: {{ $membershipSummary['total'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-500 transition-transform" :class="membershipPanelOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6" />
                </svg>
            </button>

            <div
                x-show="membershipPanelOpen"
                x-cloak
                x-transition.opacity
                class="space-y-2"
            >
                @if($membershipSummary['total'] === 0)
                    <p class="text-sm text-stone-500">Tutti i clienti sono in regola con la quota associativa per l’attuale stagione.</p>
                @else
                    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-500">
                            <span>Quote in sospeso: {{ $membershipSummary['total'] }}</span>
                            <span>Mostra {{ $membershipSummary['per_page'] }} voci per pagina (configurabile dalle impostazioni)</span>
                        </div>
                        <table class="min-w-full divide-y divide-stone-200 text-xs leading-tight">
                            <thead class="bg-stone-100 text-[11px] uppercase tracking-wider text-stone-500">
                                <tr>
                                    <th class="px-3 py-1.5 text-left font-semibold">Cliente</th>
                                    <th class="px-3 py-1.5 text-left font-semibold">Contatti</th>
                                    <th class="px-3 py-1.5 text-left font-semibold">Anno</th>
                                    <th class="px-3 py-1.5 text-left font-semibold">Importo</th>
                                    <th class="px-3 py-1.5 text-left font-semibold">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach($membershipSummary['entries'] as $entry)
                                    @php
                                        $whatsapp = preg_replace('/\\D+/', '', $entry['telephone'] ?? '');
                                    @endphp
                                    <tr class="hover:bg-stone-50">
                                        <td class="px-3 py-1.5">
                                            <p class="text-sm font-semibold text-stone-800">{{ $entry['name'] }}</p>
                                            <p class="text-[11px] text-stone-400">ID #{{ $entry['payment_id'] }}</p>
                                        </td>
                                        <td class="px-3 py-1.5 space-y-1">
                                            @if(!empty($entry['email']))
                                                <a href="mailto:{{ $entry['email'] }}" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-700">
                                                    {{ $entry['email'] }}
                                                </a>
                                            @endif
                                            @if(!empty($entry['telephone']))
                                                <div>
                                                    @if($whatsapp)
                                                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-700">
                                                            {{ $entry['telephone'] }}
                                                        </a>
                                                    @else
                                                        <span class="text-stone-600">{{ $entry['telephone'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1.5 text-sm font-semibold text-stone-600">
                                            {{ $entry['season_label'] ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 font-semibold text-stone-800">
                                            € {{ number_format($entry['amount'] ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-1.5">
                                            @if($entry['payment_id'])
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.payments.update', $entry['payment_id']) }}"
                                                    class="inline-flex"
                                                    onsubmit="return confirm('Confermi di registrare la quota associativa per {{ $entry['name'] }}?');"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="action" value="cash">
                                                    <input type="hidden" name="reason" value="">
                                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0-2.737.81-2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Paga in contanti
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[11px] text-stone-400">Pagamento non disponibile</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(($membershipSummary['last_page'] ?? 1) > 1)
                        <div class="flex flex-col gap-2 border-t border-stone-100 pt-2 text-xs text-stone-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>Pagina {{ $membershipCurrentPage }} di {{ $membershipLastPage }}</span>
                            <div class="flex items-center gap-2">
                                @php
                                    $prevPage = max(1, $membershipCurrentPage - 1);
                                    $nextPage = min($membershipLastPage, $membershipCurrentPage + 1);
                                    $pageNumbers = range(1, $membershipLastPage);
                                @endphp
                                <a
                                    href="{{ $membershipCurrentPage > 1 ? request()->fullUrlWithQuery(['membership_page' => $prevPage]) : '#' }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 font-semibold transition {{ $membershipCurrentPage > 1 ? 'text-stone-600 hover:bg-stone-100' : 'cursor-not-allowed text-stone-300' }}"
                                    @if($membershipCurrentPage <= 1) aria-disabled="true" @endif
                                >
                                    Precedente
                                </a>
                                <nav class="flex items-center gap-1">
                                    @foreach($pageNumbers as $page)
                                        <a
                                            href="{{ $page === $membershipCurrentPage ? '#' : request()->fullUrlWithQuery(['membership_page' => $page]) }}"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border px-2 text-[11px] font-semibold transition {{ $page === $membershipCurrentPage ? 'border-teal-500 bg-teal-50 text-teal-700 cursor-default' : 'border-stone-300 text-stone-600 hover:bg-stone-100' }}"
                                            @if($page === $membershipCurrentPage) aria-current="page" @endif
                                        >
                                            {{ $page }}
                                        </a>
                                    @endforeach
                                </nav>
                                <a
                                    href="{{ $membershipCurrentPage < $membershipLastPage ? request()->fullUrlWithQuery(['membership_page' => $nextPage]) : '#' }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 font-semibold transition {{ $membershipCurrentPage < $membershipLastPage ? 'text-stone-600 hover:bg-stone-100' : 'cursor-not-allowed text-stone-300' }}"
                                    @if($membershipCurrentPage >= $membershipLastPage) aria-disabled="true" @endif
                                >
                                    Successiva
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
    @if(isset($courseUnpaidSummary))
        <div class="card p-6 space-y-5" x-data="{ expandedCourse: null }">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900">Morosità corsi</h3>
                    <p class="text-sm text-stone-500">
                        Situazione aggiornata per {{ $courseUnpaidSummary['month_label'] }}. Totale clienti in ritardo: {{ $courseUnpaidSummary['total_unpaid'] }}.
                    </p>
                </div>
            </div>
            <div class="space-y-3">
                @forelse ($courseUnpaidSummary['courses'] as $summary)
                    <div class="border border-stone-200 rounded-2xl p-4 bg-white shadow-sm">
                        <button
                            type="button"
                            class="w-full flex items-center justify-between gap-3 text-left"
                            @click="expandedCourse === {{ $summary['course_id'] }} ? expandedCourse = null : expandedCourse = {{ $summary['course_id'] }}"
                            @disabled($summary['count'] === 0)
                        >
                            <div>
                                <p class="text-base font-semibold text-stone-800">{{ $summary['title'] }}</p>
                                @if(!empty($summary['plans']))
                                    <p class="text-xs text-stone-500 flex flex-wrap gap-1">
                                        @foreach ($summary['plans'] as $plan)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5">
                                                {{ $plan['label'] }} · € {{ number_format($plan['amount'] ?? 0, 2, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </p>
                                @else
                                    <p class="text-xs text-stone-500">Tariffe non configurate.</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-sm font-semibold
                                {{ $summary['count'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }}">
                                {{ $summary['count'] }}
                            </span>
                        </button>

                        <div
                            class="mt-4 space-y-3"
                            x-show="expandedCourse === {{ $summary['course_id'] }}"
                            x-cloak
                        >
                            @if ($summary['count'] === 0)
                                <p class="text-sm text-emerald-600 font-medium">Tutti i clienti sono in regola con il pagamento.</p>
                            @else
                                <div class="overflow-hidden border border-stone-200 rounded-xl">
                                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                                        <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                                                <th class="px-4 py-3 text-left font-semibold">Contatti</th>
                                                <th class="px-4 py-3 text-left font-semibold">Periodo</th>
                                                <th class="px-4 py-3 text-left font-semibold">Importo</th>
                                                <th class="px-4 py-3 text-left font-semibold">Scadenza</th>
                                                <th class="px-4 py-3 text-left font-semibold">Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-100 bg-white">
                                            @foreach ($summary['unpaid'] as $entry)
                                                @php
                                                    $whatsapp = preg_replace('/\D+/', '', $entry['client_telephone'] ?? '');
                                                @endphp
                                                <tr class="hover:bg-stone-50">
                                                    <td class="px-4 py-3">
                                                        <p class="font-semibold text-stone-800">{{ $entry['client_name'] ?? 'Cliente' }}</p>
                                                        <p class="text-xs text-stone-400">ID pagamento #{{ $entry['payment_id'] }}</p>
                                                    </td>
                                                    <td class="px-4 py-3 space-y-1 text-sm">
                                                        @if(!empty($entry['client_email']))
                                                            <a href="mailto:{{ $entry['client_email'] }}" class="text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                                                {{ $entry['client_email'] }}
                                                            </a>
                                                        @endif
                                                        @if(!empty($entry['client_telephone']))
                                                            <div>
                                                                @if($whatsapp)
                                                                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                                                        {{ $entry['client_telephone'] }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-stone-600">{{ $entry['client_telephone'] }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-stone-600">
                                                        <p class="text-sm font-semibold text-stone-700">
                                                            {{ $entry['period_label'] ?? ($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->translatedFormat('F Y') : '—') }}
                                                        </p>
                                                        @if(!empty($entry['plan_label']))
                                                            <p class="text-xs text-stone-500">Piano: {{ $entry['plan_label'] }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-stone-700 font-semibold">
                                                        € {{ number_format($entry['amount'] ?? 0, 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-stone-600">
                                                        {{ $entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-row flex-wrap items-center gap-2" x-data="{ showWaiveForm: false }">
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.payments.update', $entry['payment_id']) }}"
                                                                class="flex"
                                                                onsubmit="return confirm('Confermi di registrare in contanti il pagamento per {{ $entry['client_name'] ?? 'questo cliente' }}?');"
                                                            >
                                                                @csrf
                                                                <input type="hidden" name="action" value="cash">
                                                                <input type="hidden" name="reason" value="">
                                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition-colors hover:bg-teal-700">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                    Paga in contanti
                                                                </button>
                                                            </form>
                                                            <div>
                                                                <button
                                                                    type="button"
                                                                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-stone-200 px-2.5 py-1.5 text-[11px] font-semibold text-stone-700 transition-colors hover:bg-stone-300"
                                                                    @click="showWaiveForm = !showWaiveForm"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                    Annulla mese
                                                                </button>
                                                                <form
                                                                    method="POST"
                                                                    action="{{ route('admin.payments.update', $entry['payment_id']) }}"
                                                                    class="space-y-2"
                                                                    x-show="showWaiveForm"
                                                                    x-cloak
                                                                    onsubmit="return confirm('Confermi di annullare il mese per {{ $entry['client_name'] ?? 'questo cliente' }}?');"
                                                                >
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="waive">
                                                                    <textarea name="reason" rows="2" class="input-field text-xs" placeholder="Motivo (es. malattia)" required></textarea>
                                                                    <div class="flex items-center gap-2">
                                                                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition-colors hover:bg-teal-700">
                                                                            Conferma annulla
                                                                        </button>
                                                                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-200" @click="showWaiveForm = false">
                                                                            Annulla
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500">Nessun corso registrato al momento.</p>
                @endforelse
            </div>
        </div>
    @endif

    @include('dashboard.partials.admin-courses', ['teacherOptions' => $teacherSelectOptions, 'dayOptions' => $dayOptions])

    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-stone-900">Clienti</h3>
                <p class="text-sm text-stone-500">Gestisci dati anagrafici, stato account e pagamenti delle quote associative.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.users.export') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-4 py-2 text-xs font-semibold text-teal-600 transition-colors hover:border-teal-300 hover:bg-teal-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                    </svg>
                    Scarica elenco (.csv)
                </a>
                <button type="button" class="btn-primary text-xs self-start md:self-auto" @click="showCreateClient = !showCreateClient">
                    <span class="text-sm font-semibold" x-text="showCreateClient ? 'Nascondi form' : 'Nuovo cliente'"></span>
                </button>
            </div>
        </div>

        <form
            x-show="showCreateClient"
            x-transition
            method="POST"
            action="{{ route('admin.users.store') }}"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6"
        >
            @csrf
            <input type="hidden" name="role" value="Client">
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
                <input type="email" name="email" required class="input-field text-sm" placeholder="you@example.com">
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
                <input type="text" name="codice_fiscale" required maxlength="16" pattern="[A-Za-z0-9]{16}" oninput="this.value = this.value.toUpperCase()" class="input-field text-sm" placeholder="CODICEFISCALE16">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Registra cliente</button>
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
                        <th class="px-4 py-3 text-left font-semibold">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($clients as $client)
                        @php
                            $phoneParts = explode(' ', $client->telephone ?? '', 2);
                            $clientPrefix = $phoneParts[0] ?? '+39';
                            $clientNumber = $phoneParts[1] ?? '';
                            $clientWhatsapp = preg_replace('/\D+/', '', $client->telephone ?? '');

                            $documentCollection = collect($client->getAttribute('admin_documents') ?? []);
                            $documentsByType = $documentCollection->keyBy('type');
                            $missingDocuments = collect(array_keys($documentDefinitions))
                                ->reject(fn ($type) => $documentsByType->has($type))
                                ->values();
                            $pendingPaymentsCount = (int) ($client->getAttribute('admin_pending_payments_count') ?? 0);
                        @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-stone-600">
                                @if ($client->email)
                                    <a href="mailto:{{ $client->email }}" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">{{ $client->email }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                @if ($client->telephone && $clientWhatsapp)
                                    <a href="https://wa.me/{{ $clientWhatsapp }}" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        {{ $client->telephone }}
                                    </a>
                                @else
                                    {{ $client->telephone ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($client->status === 'active') bg-emerald-100 text-emerald-700
                                    @elseif($client->status === 'pending') bg-amber-100 text-amber-700
                                    @else bg-rose-100 text-rose-700 @endif">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleClient({{ $client->id }})">
                                    <span x-text="expandedClient === {{ $client->id }} ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedClient === {{ $client->id }}" x-cloak x-transition>
                            <td colspan="5" class="px-4 pb-5">
                                <div x-data="{ showProfile: false }" class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Dettagli cliente</h4>
            <p class="text-xs text-stone-500">Aggiorna le informazioni anagrafiche, lo stato dell'account e gli accessi.</p>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                @if($missingDocuments->isEmpty())
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Documenti completi</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l5.451 9.698c.75 1.335-.213 3.003-1.742 3.003H4.548c-1.53 0-2.492-1.668-1.743-3.003l5.452-9.698zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $missingDocuments->count() }} documenti mancanti</span>
                    </span>
                @endif

                @if($pendingPaymentsCount > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-1 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.75a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0v-4.5zM10 13a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $pendingPaymentsCount }} pagamenti da gestire</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Pagamenti regolari</span>
                    </span>
                @endif
            </div>
        </div>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-100"
            @click="showProfile = !showProfile"
            x-text="showProfile ? 'Nascondi dati' : 'Mostra dati'"
        ></button>
    </div>

    <form method="POST" action="{{ route('admin.users.profile', $client) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div x-show="showProfile" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                <input type="text" name="first_name" value="{{ $client->first_name }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" value="{{ $client->last_name }}" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" value="{{ $client->email }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                <select name="telephone_country" class="input-field text-sm">
                    @foreach ($phonePrefixes as $option)
                        <option value="{{ $option['code'] }}" @selected($clientPrefix === $option['code'])>{{ $option['name'] }} ({{ $option['code'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                <input type="text" name="telephone" value="{{ $clientNumber }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                <input type="text" name="residenza_citta" value="{{ $client->residenza_citta }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                <input type="text" name="residenza_provincia" value="{{ $client->residenza_provincia }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                <input type="text" name="residenza_stato" value="{{ $client->residenza_stato }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                <input type="text" name="residenza_via" value="{{ $client->residenza_via }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                <input type="text" name="residenza_numero_civico" value="{{ $client->residenza_numero_civico }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                <input type="text" name="codice_fiscale" value="{{ $client->codice_fiscale }}" required class="input-field text-sm uppercase">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" value="{{ $client->luogo_nascita }}" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" value="{{ optional($client->data_nascita)->format('Y-m-d') }}" required class="input-field text-sm">
            </div>
        </div>

        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <label for="status-{{ $client->id }}" class="text-xs uppercase text-stone-500 font-semibold">Stato account</label>
                <select id="status-{{ $client->id }}" name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                    @foreach (['active', 'pending', 'disabled'] as $statusOption)
                        <option value="{{ $statusOption }}" @selected($client->status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 md:justify-end">
                <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Verifica Email:</span>
                @if($client->email_verified_at)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ optional($client->email_verified_at)->format('d/m/Y H:i') }}</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-1 text-rose-600 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span>Non verificata</span>
                    </span>
                    <form method="POST" action="{{ route('admin.users.resendVerification', $client) }}" class="inline-flex"
                        onsubmit="return confirm('Inviare una nuova email di verifica a {{ $client->email }}?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-amber-600 transition-colors">
                            Reinvia verifica
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.users.passwordEmail', $client) }}" class="inline-flex"
                    onsubmit="return confirm('Vuoi inviare un\'email di reset password a {{ $client->email }}? L\'utente riceverà un link per impostare una nuova password.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-rose-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-rose-600 transition-colors">
                        Invia reset password
                    </button>
                </form>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700 transition"
                    onclick="return confirm('Salvare le modifiche per {{ $client->name }}? Verranno aggiornati i dati del profilo.');">
                    Salva dati
                </button>
            </div>

            <div
                x-show="showProfile"
                x-cloak
                x-data="{
                    definitions: {{ \Illuminate\Support\Js::from($documentDefinitions) }},
                    docs: {{ \Illuminate\Support\Js::from($documentCollection->values()) }},
                    uploadUrl: '{{ route('admin.users.documents.store', $client, false) }}',
                    csrf: '{{ csrf_token() }}',
                    message: null,
                    messageType: 'success',
                    _timeout: null,
                    docByType(type) {
                        return this.docs.find(doc => doc.type === type) || null;
                    },
                    entries() {
                        return Object.entries(this.definitions).map(([type, label]) => {
                            const doc = this.docByType(type);
                            return {
                                type,
                                label,
                                present: !!doc,
                                url: doc ? (doc.download_url || doc.downloadUrl || doc.url) : null,
                                uploaded_at_display: doc ? (doc.uploaded_at_display ?? doc.uploadedAtDisplay ?? '') : null,
                                original_name: doc ? (doc.original_name ?? doc.originalName ?? '') : null,
                            };
                        });
                    },
                    setMessage(text, type = 'success') {
                        this.message = text;
                        this.messageType = type;
                        clearTimeout(this._timeout);
                        this._timeout = setTimeout(() => {
                            this.message = null;
                        }, 3000);
                    },
                    async handleFile(event, type) {
                        const file = event.target.files[0];
                        if (!file) {
                            return;
                        }
                        const formData = new FormData();
                        formData.append('document_type', type);
                        formData.append('document_file', file);
                        formData.append('_token', this.csrf);
                        try {
                            const response = await fetch(this.uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                                credentials: 'same-origin',
                            });
                            const raw = await response.text();
                            let data = {};
                            try {
                                data = raw ? JSON.parse(raw) : {};
                            } catch (parseError) {
                                data = { message: raw?.trim() ?? null };
                            }
                            if (!response.ok || !data.document) {
                                throw new Error(data.message || response.statusText || 'Caricamento non riuscito');
                            }
                            const doc = Object.assign({}, data.document, {
                                uploaded_at_display: data.document.uploaded_at_display ?? data.document.uploadedAtDisplay ?? '',
                                original_name: data.document.original_name ?? data.document.originalName ?? '',
                                download_url: data.document.download_url ?? data.document.downloadUrl ?? data.document.url ?? null,
                            });
                            const index = this.docs.findIndex(existing => existing.type === type);
                            if (index >= 0) {
                                this.docs.splice(index, 1, doc);
                            } else {
                                this.docs.push(doc);
                            }
                            this.setMessage(data.message || 'Documento aggiornato correttamente', 'success');
                        } catch (error) {
                            this.setMessage(error.message || 'Errore durante il caricamento', 'error');
                        } finally {
                            event.target.value = '';
                        }
                    },
                }"
                class="border border-dashed border-stone-300 rounded-xl bg-white/60 px-3 py-2"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Documenti personali</p>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <template x-for="entry in entries()" :key="entry.type">
                        <div class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white/80 px-3 py-1 shadow-sm">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full"
                                :class="entry.present ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'">
                                <template x-if="entry.present">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template x-if="!entry.present">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l5.451 9.698c.75 1.335-.213 3.003-1.742 3.003H4.548c-1.53 0-2.492-1.668-1.743-3.003l5.452-9.698zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                            </span>
                            <span class="font-semibold text-stone-700" x-text="entry.label"></span>
                            <span class="text-[10px] text-stone-400" x-text="entry.present ? (entry.uploaded_at_display || entry.original_name || 'Aggiornato') : 'Manca documento'"></span>
                            <div class="flex items-center gap-1">
                                <template x-if="entry.present && entry.url">
                                    <a :href="entry.url" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-md border border-teal-200 px-2 py-0.5 text-[10px] font-semibold text-teal-600 hover:bg-teal-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                        </svg>
                                        Scarica
                                    </a>
                                </template>
                                <label class="inline-flex items-center gap-1 rounded-md border border-stone-300 px-2 py-0.5 text-[10px] font-semibold text-stone-600 hover:bg-stone-100 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l3.5-3.5M12 16l-3.5-3.5" />
                                    </svg>
                                    Carica
                                    <input type="file" class="sr-only" accept=".pdf,image/*" @change="handleFile($event, entry.type)">
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
                <div
                    x-show="message"
                    x-transition
                    class="fixed top-4 right-4 z-50 rounded-xl border px-4 py-2 text-xs font-semibold shadow-lg"
                    :class="messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'"
                    x-text="message"
                ></div>
            </div>
    </form>
</div>
@if($client->current_membership)
        <div class="border border-stone-200 rounded-lg px-4 py-3 bg-white space-y-2 text-xs text-stone-600">
            <p class="font-semibold text-stone-700 uppercase tracking-wide">Quota {{ $client->current_membership->season_start_year }}/{{ $client->current_membership->season_start_year + 1 }}</p>
            <div class="flex flex-wrap items-center gap-3">
                <span>Scadenza: <strong>{{ optional($client->current_membership->due_date)->format('d/m/Y') ?? '—' }}</strong></span>
                <span>Importo: <strong>€ {{ number_format($client->current_membership->amount ?? 0, 2, ',', '.') }}</strong></span>
                <span>Stato: <strong>{{ ucfirst($client->current_membership->status) }}</strong></span>
                <span>Pagato il: <strong>{{ optional($client->current_membership->paid_at)->format('d/m/Y H:i') ?? '—' }}</strong></span>
            </div>
            @if(optional($client->membership_payment)?->receipt_url)
                <div class="mt-2">
                    <a href="{{ route('admin.payments.receipt', optional($client->membership_payment)->id) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        Scarica ricevuta
                    </a>
                </div>
            @endif
        </div>
    @endif

@php
                                                $adminPaymentsAll = $client->getAttribute('admin_payments_all');
                                                $adminPaymentsAllArray = $adminPaymentsAll instanceof \Illuminate\Support\Collection ? $adminPaymentsAll->values()->toArray() : [];
                                                $currentCalendarYear = now()->year;
                                            @endphp
                                            <div
                                                x-data="{
                                                    showAll: false,
                                                    sortField: 'created_at',
                                                    sortDirection: 'desc',
                                                    currentYear: {{ $currentCalendarYear }},
                                                    paymentsAll: @js($adminPaymentsAllArray),
                                                    get historyAvailable() {
                                                        return this.paymentsAll.some(payment => (payment.year ?? null) !== this.currentYear);
                                                    },
                                                    get dataset() {
                                                        const source = this.showAll
                                                            ? this.paymentsAll
                                                            : this.paymentsAll.filter(payment => (payment.year ?? null) === this.currentYear);
                                                        const direction = this.sortDirection === 'asc' ? 1 : -1;
                                                        const field = this.sortField;

                                                        return [...source].sort((a, b) => {
                                                            if (field === 'amount') {
                                                                return ((a.amount ?? 0) - (b.amount ?? 0)) * direction;
                                                            }

                                                            const aValue = a[field] ?? '';
                                                            const bValue = b[field] ?? '';

                                                            if (field === 'created_at') {
                                                                if (aValue === bValue) {
                                                                    return 0;
                                                                }

                                                                return (aValue > bValue ? 1 : -1) * direction;
                                                            }

                                                            return String(aValue).localeCompare(String(bValue), 'it', { numeric: true, sensitivity: 'base' }) * direction;
                                                        });
                                                    },
                                                    toggleSort(field) {
                                                        if (this.sortField === field) {
                                                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                                                        } else {
                                                            this.sortField = field;
                                                            this.sortDirection = field === 'created_at' ? 'desc' : 'asc';
                                                        }
                                                    },
                                                    sortIndicator(field) {
                                                        if (this.sortField !== field) {
                                                            return '⇅';
                                                        }

                                                        return this.sortDirection === 'asc' ? '↑' : '↓';
                                                    }
                                                }"
                                                class="w-full border border-stone-200 rounded-lg px-4 py-4 bg-white space-y-3 text-xs text-stone-600"
                                            >
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <p class="text-xs uppercase text-stone-500 font-semibold">Pagamenti</p>
                                                        <p class="text-sm text-stone-600" x-text="showAll ? 'Storico completo' : `Anno ${currentYear}`"></p>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-stone-500" x-text="`${dataset.length} risultati`"></span>
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 transition hover:bg-stone-100"
                                                            x-show="historyAvailable"
                                                            x-cloak
                                                            @click="showAll = !showAll"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                            <span x-text="showAll ? 'Mostra anno corrente' : 'Mostra storico completo'"></span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div
                                                    class="overflow-hidden rounded-lg border border-stone-200"
                                                    x-show="dataset.length > 0"
                                                    x-cloak
                                                >
                                                    <table class="min-w-full divide-y divide-stone-200 text-xs leading-tight">
                                                        <thead class="bg-stone-100 text-[11px] uppercase tracking-wider text-stone-500">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('created_at')">
                                                                        Data
                                                                        <span class="text-[10px]" x-text="sortIndicator('created_at')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('type_label')">
                                                                        Tipo
                                                                        <span class="text-[10px]" x-text="sortIndicator('type_label')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('amount')">
                                                                        Importo
                                                                        <span class="text-[10px]" x-text="sortIndicator('amount')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('status_label')">
                                                                        Stato
                                                                        <span class="text-[10px]" x-text="sortIndicator('status_label')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('due_date')">
                                                                        Scadenza
                                                                        <span class="text-[10px]" x-text="sortIndicator('due_date')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">Azioni</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-stone-100 bg-white">
                                                            <template x-for="payment in dataset" :key="`payment-${payment.id}`">
                                                                <tr class="hover:bg-stone-50" x-data="{ showWaiveForm: false }">
                                                                    <td class="px-3 py-2 align-top">
                                                                        <p class="text-sm font-semibold text-stone-800" x-text="payment.created_at_display ?? '—'"></p>
                                                                        <p class="text-[11px] text-stone-400" x-text="payment.year ? `Anno ${payment.year}` : ''"></p>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="text-sm font-semibold text-stone-800" x-text="payment.type_label"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="font-semibold text-stone-800">€ <span x-text="payment.amount_formatted"></span></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="payment.status_badge_class" x-text="payment.status_label"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span x-text="payment.due_date_display ?? '—'"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <div class="space-y-2">
                                                                            <div class="flex flex-wrap items-center gap-2">
                                                                                <template x-if="payment.routes.receipt">
                                                                                    <a
                                                                                        :href="payment.routes.receipt"
                                                                                        target="_blank"
                                                                                        rel="noopener"
                                                                                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-2 py-1 text-[10px] font-semibold text-white hover:bg-teal-700"
                                                                                    >
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                                                                                        </svg>
                                                                                        Ricevuta
                                                                                    </a>
                                                                                </template>
                                                                                <template x-if="payment.receipt_available && payment.routes.reprint">
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.reprint"
                                                                                        class="inline-flex"
                                                                                        onsubmit="return confirm('Confermi di ristampare la ricevuta? Il documento esistente verrà archiviato.');"
                                                                                    >
                                                                                        @csrf
                                                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-2 py-1 text-[10px] font-semibold text-stone-600 hover:bg-stone-100 transition-colors">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7v6a2 2 0 01-2 2H9l-4 4V9a2 2 0 012-2h2"/>
                                                                                            </svg>
                                                                                            Ristampa
                                                                                        </button>
                                                                                    </form>
                                                                                </template>
                                                                                <template x-if="payment.is_pending">
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.update"
                                                                                        class="inline-flex"
                                                                                        onsubmit="return confirm('Confermi di registrare questo pagamento in contanti?');"
                                                                                    >
                                                                                        @csrf
                                                                                        <input type="hidden" name="action" value="cash">
                                                                                        <input type="hidden" name="reason" value="">
                                                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0-2.737.81-2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                            </svg>
                                                                                            Paga in contanti
                                                                                        </button>
                                                                                    </form>
                                                                                </template>
                                                                            </div>
                                                                            <template x-if="payment.is_pending && payment.is_course">
                                                                                <div class="space-y-2">
                                                                                    <button
                                                                                        type="button"
                                                                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-200 px-3 py-1.5 text-[11px] font-semibold text-stone-700 hover:bg-stone-300 transition-colors"
                                                                                        @click="showWaiveForm = !showWaiveForm"
                                                                                    >
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                        </svg>
                                                                                        <span x-text="showWaiveForm ? 'Nascondi annulla' : 'Annulla mese'"></span>
                                                                                    </button>
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.update"
                                                                                        class="space-y-2"
                                                                                        x-show="showWaiveForm"
                                                                                        x-cloak
                                                                                        onsubmit="return confirm('Confermi di annullare il mese per questo cliente?');"
                                                                                    >
                                                                                        @csrf
                                                                                        <input type="hidden" name="action" value="waive">
                                                                                        <textarea name="reason" rows="2" class="input-field text-xs" placeholder="Motivo (es. malattia)" required></textarea>
                                                                                        <div class="flex items-center gap-2">
                                                                                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                                Conferma annulla
                                                                                            </button>
                                                                                            <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-stone-100 px-2.5 py-1.5 text-[11px] font-semibold text-stone-600 hover:bg-stone-200" @click="showWaiveForm = false">
                                                                                                Annulla
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div
                                                    class="rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-500 space-y-2"
                                                    x-show="dataset.length === 0"
                                                    x-cloak
                                                >
                                                    <p x-text="showAll ? 'Nessun pagamento registrato per questo cliente.' : `Nessun pagamento registrato per l\'anno ${currentYear}.`"></p>
                                                    <template x-if="!showAll && historyAvailable">
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                                                            @click="showAll = true"
                                                        >
                                                            Visualizza storico completo
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Nessun cliente registrato al momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-stone-900">Docenti</h3>
                <p class="text-sm text-stone-500">Gestisci credenziali, lezioni private e assegnazioni dei corsi mensili.</p>
            </div>
            <button type="button" class="btn-primary text-xs self-start md:self-auto" @click="showCreateTeacher = !showCreateTeacher">
                <span class="text-sm font-semibold" x-text="showCreateTeacher ? 'Nascondi form docente' : 'Nuovo docente'"></span>
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
                <input type="text" name="first_name" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Password temporanea</label>
                <input type="password" name="password" minlength="6" required class="input-field text-sm">
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
                <input type="text" name="telephone" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2 flex items-center gap-2">
                <input id="teacher-private" type="checkbox" name="can_host_private" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                <label for="teacher-private" class="text-sm text-stone-600">Abilita immediatamente le lezioni private</label>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Registra docente</button>
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
                            $assignedCourses = $teacher->courses->pluck('id')->all();
                            $teacherWhatsapp = preg_replace('/\D+/', '', $teacherUser->telephone ?? '');
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
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <form method="POST" action="{{ route('admin.users.profile', $teacherUser) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
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
                                            <div class="md:col-span-2">
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
                                            <div class="md:col-span-2 flex justify-end">
                                                <button type="submit" class="btn-primary text-sm">Salva dati</button>
                                            </div>
                                        </form>

                                        <div class="space-y-4">
                                            <form method="POST" action="{{ route('admin.users.update', $teacherUser) }}" class="flex flex-col md:flex-row md:items-center md:gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="role" value="Teacher">
                                                <select name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                                    @foreach (['active', 'pending', 'disabled'] as $statusOption)
                                                        <option value="{{ $statusOption }}" @selected($teacherUser->status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="bg-teal-600 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">Aggiorna stato</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.passwordEmail', $teacherUser) }}" class="flex items-center gap-3">
                                                @csrf
                                                <button type="submit" class="text-xs bg-rose-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-rose-600 transition-colors">Invia reset password</button>
                                            </form>

                                            <div class="flex flex-wrap items-center gap-2 text-xs text-stone-600">
                                                <span class="font-semibold text-stone-700 uppercase tracking-wide"> Verifica Email:</span>
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

                                            <form method="POST" action="{{ route('admin.teachers.private', $teacher) }}" class="flex items-center gap-3">
                                                @csrf
                                                <input type="hidden" name="can_host_private" value="0">
                                                <label class="flex items-center gap-2 text-xs text-stone-600">
                                                    <input type="checkbox" name="can_host_private" value="1" @checked($teacher->can_host_private) class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                    Abilita lezioni private
                                                </label>
                                                <button type="submit" class="text-xs bg-teal-600 text-white font-semibold px-3 py-2 rounded-lg hover:bg-teal-700 transition-colors">Aggiorna</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.teachers.courses', $teacher) }}" class="space-y-2">
                                                @csrf
                                                <p class="text-xs uppercase text-stone-500 font-semibold">Assegna corsi</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-stone-200 rounded-lg p-3 bg-white text-xs">
                                                    @foreach ($courses as $course)
                                                        <label class="flex items-center gap-2">
                                                            <input type="checkbox" name="course_ids[]" value="{{ $course['id'] }}" @checked(in_array($course['id'], $assignedCourses)) class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                            <span>{{ $course['title'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <button type="submit" class="btn-primary text-xs">Salva assegnazioni</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Nessun docente registrato al momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
