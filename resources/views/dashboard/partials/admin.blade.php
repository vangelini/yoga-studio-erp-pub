@php
    $phonePrefixes = [
        ['code' => '+39', 'name' => 'Italia'],
        ['code' => '+33', 'name' => 'Francia'],
        ['code' => '+49', 'name' => 'Germania'],
        ['code' => '+34', 'name' => 'Spagna'],
        ['code' => '+44', 'name' => 'Regno Unito'],
        ['code' => '+1', 'name' => 'Stati Uniti'],
    ];

    $viewConfig = $dashboardViewConfig ?? [];
    $showClientAdmin = $viewConfig['show_client_admin'] ?? true;
    $showTeacherAdmin = $viewConfig['show_teacher_admin'] ?? true;
    $showCourseAdmin = $viewConfig['show_course_admin'] ?? true;
    $allowCourseCreation = $viewConfig['allow_course_creation'] ?? true;
    $allowTeacherSelection = $viewConfig['allow_teacher_selection'] ?? true;
    $allowStudentManage = $viewConfig['allow_student_manage'] ?? true;
    $showSettings = $viewConfig['show_settings'] ?? true;
    $courseCardTitle = $viewConfig['course_card_title'] ?? null;
    $courseCardSubtitle = $viewConfig['course_card_subtitle'] ?? null;
    $courseCardTeacherId = $viewConfig['current_teacher_id'] ?? null;

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
        membershipPanelOpen: @json(request()->has('membership_page')),
    }"
    class="space-y-12"
>
    <div class="relative overflow-hidden rounded-2xl border border-teal-200/40 bg-gradient-to-r from-teal-600 via-teal-500 to-emerald-500 text-white shadow-lg">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/honeycomb.png')] opacity-20 pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Pannello amministrazione</p>
                <h2 class="text-3xl md:text-4xl font-semibold">Gestisci associati, insegnanti e corsi</h2>
                <p class="text-white/85 leading-relaxed">
                    Verifica i dati degli iscritti, assegna corsi ai insegnanti e monitora pagamenti e quote associative in un unico posto.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    @if($showSettings)
                    <a href="{{ route('admin.settings.edit') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 3.75a1.5 1.5 0 013 0V5a1.5 1.5 0 01-3 0V3.75zM5.636 5.636a1.5 1.5 0 010 2.121l-.884.884a1.5 1.5 0 01-2.122-2.121l.884-.884a1.5 1.5 0 012.122 0zM3.75 10.5H5a1.5 1.5 0 010 3H3.75a1.5 1.5 0 010-3zM5.636 18.364a1.5 1.5 0 01-2.122 0l-.884-.884a1.5 1.5 0 112.122-2.121l.884.884a1.5 1.5 0 000 2.121zM10.5 18.75V20a1.5 1.5 0 003 0v-1.25a1.5 1.5 0 00-3 0zM18.364 18.364a1.5 1.5 0 002.122 0l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 000 2.121zM20.25 13.5H19a1.5 1.5 0 110-3h1.25a1.5 1.5 0 110 3zM18.364 5.636l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 002.122 2.121z"/>
                        </svg>
                        Impostazioni
                    </a>
                    @endif
                    @if($showClientAdmin)
                    <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 9.75h15m-13.5 3H12m-7.5 3H12m6.75-6v6.75a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 016.75 4.5h9a2.25 2.25 0 012.25 2.25V9.75z" />
                        </svg>
                        Amministrazione allieve/i
                    </a>
                    @endif
                    @if($showTeacherAdmin)
                    <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l-3 3m3-3l3 3m-3-3V4m9 5v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9" />
                        </svg>
                        Amministrazione insegnanti
                    </a>
                    @endif
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
                    <span>insegnanti</span>
                    <span class="text-2xl font-semibold text-white">{{ $teacherCount }}</span>
                </div>
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
                    
                            <span>Mostra {{ $membershipSummary['per_page'] }} voci per pagina (configurabile dalle impostazioni)</span>
                        </div>
                        <table class="min-w-full divide-y divide-stone-200 text-xs leading-tight">
                            <thead class="bg-stone-100 text-[11px] uppercase tracking-wider text-stone-500">
                                <tr>
                                    <th class="px-3 py-1.5 text-left font-semibold">allieva/o</th>
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
                                            <p class="text-[11px] text-stone-400">ID pagamento #{{ $entry['payment_id'] }}</p>
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
        @php $showFutureCourses = !empty($courseUnpaidShowFuture); @endphp
        @php $courseFilterParams = request()->except('show_future_course_payments'); @endphp
        <div class="card p-6 space-y-5" x-data="{ expandedCourse: null }">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900">Morosità corsi</h3>
                    @if($showFutureCourses)
                        <p class="text-sm text-stone-500">
                            Sono visualizzate tutte le scadenze pendenti: {{ $courseUnpaidSummary['total_unpaid'] }} (di cui {{ $courseUnpaidSummary['future_total'] ?? 0 }} future).
                        </p>
                    @else
                        <p class="text-sm text-stone-500">
                            Situazione aggiornata per {{ $courseUnpaidSummary['month_label'] }}. Totale clienti in ritardo: {{ $courseUnpaidSummary['total_unpaid'] }}.
                        </p>
                    @endif
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-stone-600">
                    @php
                        foreach ($courseFilterParams as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $item) {
                                    echo '<input type="hidden" name="'.e($key).'[]" value="'.e($item).'">';
                                }
                            } else {
                                echo '<input type="hidden" name="'.e($key).'" value="'.e($value).'">';
                            }
                        }
                    @endphp
                    <input type="hidden" name="show_future_course_payments" value="0">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="show_future_course_payments"
                            value="1"
                            class="rounded border-stone-300 text-teal-600 focus:ring-teal-500"
                            @checked($showFutureCourses)
                            onchange="this.form.submit()"
                        >
                        <span>Mostra morosità prossimo mese</span>
                    </label>
                </form>
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
                        @if($showFutureCourses && (($summary['future_count'] ?? 0) > 0))
                            <p class="mt-2 text-xs text-amber-600 font-semibold">
                                Include {{ $summary['future_count'] }} scadenze future.
                            </p>
                        @endif

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
                                                <th class="px-4 py-3 text-left font-semibold">allieva/o</th>
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
                                                        <p class="font-semibold text-stone-800">{{ $entry['client_name'] ?? 'Allieva-o' }}</p>
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
                                                        @if(!empty($entry['is_future']))
                                                            <span class="inline-flex items-center gap-1 mt-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 uppercase">
                                                                Futuro
                                                            </span>
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
                                                                onsubmit="return confirm('Confermi di registrare in contanti il pagamento per {{ $entry['client_name'] ?? 'questa allieva-o' }}?');"
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
                                                                    onsubmit="return confirm('Confermi di annullare il mese per {{ $entry['client_name'] ?? 'questo allieva/o' }}?');"
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

    @include('dashboard.partials.admin-courses', [
        'teacherOptions' => $teacherSelectOptions,
        'dayOptions' => $dayOptions,
        'allowCourseCreation' => $allowCourseCreation,
        'allowTeacherSelection' => $allowTeacherSelection,
        'courseCardTitle' => $courseCardTitle,
        'courseCardSubtitle' => $courseCardSubtitle,
        'currentTeacherId' => $courseCardTeacherId,
        'allowStudentManage' => $allowStudentManage,
    ])
</section>
