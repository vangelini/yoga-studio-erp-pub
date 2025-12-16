<?php
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
    $coursesOnly = request()->boolean('courses_only');

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
?>
<section
    x-data="{
        membershipPanelOpen: <?php echo json_encode((bool) request()->boolean('membership_open'), 15, 512) ?>,
        toggleMembershipPanel() {
            this.membershipPanelOpen = !this.membershipPanelOpen;
            const url = new URL(window.location);
            if (this.membershipPanelOpen) {
                url.searchParams.set('membership_open', '1');
            } else {
                url.searchParams.delete('membership_open');
                url.searchParams.delete('membership_page');
            }
            window.history.replaceState({}, '', url);
        },
    }"
    class="space-y-12"
>
    <?php if(session('status')): ?>
        <div class="rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm shadow-sm">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="rounded-lg border border-rose-300 bg-rose-50 text-rose-800 px-4 py-3 text-sm shadow-sm">
            <p class="font-semibold mb-1">Controlla i campi e riprova:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php if($errors->has('subscription')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Prova a chiudere la modale iscrizione se aperta
                    if (window.subscriptionModal) {
                        subscriptionModal.open = false;
                    } else {
                        // fallback: invia un evento che l'istanza Alpine può intercettare
                        window.dispatchEvent(new CustomEvent('close-subscription-modal'));
                    }
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <div class="relative overflow-hidden rounded-2xl border border-teal-200/40 bg-gradient-to-r from-teal-600 via-teal-500 to-emerald-500 text-white shadow-lg">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/honeycomb.png')] opacity-20 pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Pannello amministrazione</p>
                <h2 class="text-3xl md:text-4xl font-semibold">Gestisci associati, insegnanti e corsi</h2>
                <p class="text-white/85 leading-relaxed">
                    Verifica i dati degli iscritti, assegna corsi ai insegnanti e monitora pagamenti e quote associative in un unico posto.
                </p>

            </div>
            <div class="grid grid-cols-3 gap-4 bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/30 shadow-inner text-center text-xs uppercase tracking-widest">
                <div class="flex flex-col text-white/80">
                    <span>Allieve/i</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($clientCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Corsi</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($courseCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Insegnanti</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherCount); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$coursesOnly && isset($membershipSummary)): ?>
        <?php
            $membershipCurrentPage = $membershipSummary['current_page'] ?? 1;
            $membershipLastPage = $membershipSummary['last_page'] ?? 1;
        ?>
        <div class="card p-3 space-y-2">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-left transition hover:border-stone-300 hover:bg-stone-100"
                @click="toggleMembershipPanel()"
            >
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-500 transition-transform" :class="membershipPanelOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6" />
                </svg>Morosità quote associative</h3>
                
                </div>
                <span class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-sm font-semibold
                                <?php echo e($membershipSummary['total'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'); ?>">
                                <?php echo e($membershipSummary['total']); ?>

                            </span>
                
            </button>

            <div
                x-show="membershipPanelOpen"
                x-cloak
                x-transition.opacity
                class="space-y-2"
            >
                <?php if($membershipSummary['total'] === 0): ?>
                    <p class="text-sm text-stone-500">Tutti i clienti sono in regola con la quota associativa per l'attuale stagione.</p>
                <?php else: ?>
                    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-500">
                            <span>Mostra <?php echo e($membershipSummary['per_page']); ?> voci per pagina</span>
                            <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="inline">
                                <?php $__currentLoopData = request()->except('membership_page', 'per_page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <input type="hidden" name="membership_page" value="1">
                                <?php if(request()->boolean('membership_open')): ?>
                                    <input type="hidden" name="membership_open" value="1">
                                <?php endif; ?>
                                <select name="per_page" class="input-field text-[11px] py-1 h-8 inline-block w-auto align-middle" onchange="this.form.submit()">
                                    <?php $__currentLoopData = [25, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option); ?>" <?php if($membershipSummary['per_page'] == $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
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
                                <?php $__currentLoopData = $membershipSummary['entries']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $whatsapp = preg_replace('/\\D+/', '', $entry['telephone'] ?? '');
                                    ?>
                                    <tr class="hover:bg-stone-50">
                                        <td class="px-3 py-1.5">
                                            <p class="text-sm font-semibold text-stone-800"><?php echo e($entry['name']); ?></p>
                                            <p class="text-[11px] text-stone-400">ID pagamento #<?php echo e($entry['payment_id']); ?></p>
                                        </td>
                                        <td class="px-3 py-1.5 space-y-1">
                                            <?php if(!empty($entry['email'])): ?>
                                                <a href="mailto:<?php echo e($entry['email']); ?>" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-700">
                                                    <?php echo e($entry['email']); ?>

                                                </a>
                                            <?php endif; ?>
                                            <?php if(!empty($entry['telephone'])): ?>
                                                <div>
                                                    <?php if($whatsapp): ?>
                                                        <a href="https://wa.me/<?php echo e($whatsapp); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-700">
                                                            <?php echo e($entry['telephone']); ?>

                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-stone-600"><?php echo e($entry['telephone']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-1.5 text-sm font-semibold text-stone-600">
                                            <?php echo e($entry['season_label'] ?? '—'); ?>

                                        </td>
                                        <td class="px-3 py-1.5 font-semibold text-stone-800">
                                            € <?php echo e(number_format($entry['amount'] ?? 0, 2, ',', '.')); ?>

                                        </td>
                                        <td class="px-3 py-1.5">
                                            <?php if($entry['payment_id']): ?>
                                                <form
                                                    method="POST"
                                                    action="<?php echo e(route('admin.payments.update', $entry['payment_id'])); ?>"
                                                    class="inline-flex flex-col gap-1"
                                                    onsubmit="return confirm('Confermi di registrare la quota associativa per <?php echo e($entry['name']); ?>?');"
                                                >
                                                    <?php echo csrf_field(); ?>
                                                    <label class="sr-only" for="payment-method-<?php echo e($entry['payment_id'] ?? 'membership'); ?>">Metodo</label>
                                                    <select
                                                        id="payment-method-<?php echo e($entry['payment_id'] ?? 'membership'); ?>"
                                                        name="action"
                                                        class="input-field text-[11px] py-1 h-8"
                                                        x-data
                                                        @change="const ref = $el.closest('form').querySelector('[data-transfer-reference]'); ref && (ref.classList.toggle('hidden', $el.value !== 'bank_transfer')); if($el.value !== 'bank_transfer' && ref){ ref.value=''; }"
                                                    >
                                                        <option value="cash">Contanti</option>
                                                        <option value="bank_transfer">Bonifico</option>
                                                    </select>
                                                    <input type="hidden" name="reason" value="">
                                                    <input type="number" step="0.01" name="amount" placeholder="Importo"
                                                        class="input-field text-[11px] py-1 h-8" value="<?php echo e($entry['amount'] ?? ''); ?>">
                                                    <input type="text" name="transfer_reference" placeholder="CRO / Riferimento bonifico"
                                                        class="input-field text-[11px] py-1 h-8 hidden" data-transfer-reference>
                                                    <input type="text" name="note" placeholder="Nota (opzionale)"
                                                        class="input-field text-[11px] py-1 h-8">
                                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0-2.737.81-2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Registra pagamento
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-[11px] text-stone-400">Pagamento non disponibile</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(($membershipSummary['last_page'] ?? 1) > 1): ?>
                        <div class="flex flex-col gap-2 border-t border-stone-100 pt-2 text-xs text-stone-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>Pagina <?php echo e($membershipCurrentPage); ?> di <?php echo e($membershipLastPage); ?></span>
                            <div class="flex items-center gap-2">
                                <?php
                                    $prevPage = max(1, $membershipCurrentPage - 1);
                                    $nextPage = min($membershipLastPage, $membershipCurrentPage + 1);
                                    $pageNumbers = range(1, $membershipLastPage);
                                ?>
                               <a
                                    href="<?php echo e($membershipCurrentPage > 1 ? request()->fullUrlWithQuery(['membership_page' => $prevPage] + (request()->boolean('membership_open') ? ['membership_open' => 1] : [])) : '#'); ?>"
                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 font-semibold transition <?php echo e($membershipCurrentPage > 1 ? 'text-stone-600 hover:bg-stone-100' : 'cursor-not-allowed text-stone-300'); ?>"
                                    <?php if($membershipCurrentPage <= 1): ?> aria-disabled="true" <?php endif; ?>
                                >
                                    Precedente
                                </a>
                                <nav class="flex items-center gap-1">
                                    <?php $__currentLoopData = $pageNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                       <a
                                            href="<?php echo e($page === $membershipCurrentPage ? '#' : request()->fullUrlWithQuery(['membership_page' => $page] + (request()->boolean('membership_open') ? ['membership_open' => 1] : []))); ?>"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border px-2 text-[11px] font-semibold transition <?php echo e($page === $membershipCurrentPage ? 'border-teal-500 bg-teal-50 text-teal-700 cursor-default' : 'border-stone-300 text-stone-600 hover:bg-stone-100'); ?>"
                                            <?php if($page === $membershipCurrentPage): ?> aria-current="page" <?php endif; ?>
                                        >
                                            <?php echo e($page); ?>

                                        </a>
                                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                               </nav>
                               <a
                                    href="<?php echo e($membershipCurrentPage < $membershipLastPage ? request()->fullUrlWithQuery(['membership_page' => $nextPage] + (request()->boolean('membership_open') ? ['membership_open' => 1] : [])) : '#'); ?>"
                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 font-semibold transition <?php echo e($membershipCurrentPage < $membershipLastPage ? 'text-stone-600 hover:bg-stone-100' : 'cursor-not-allowed text-stone-300'); ?>"
                                    <?php if($membershipCurrentPage >= $membershipLastPage): ?> aria-disabled="true" <?php endif; ?>
                                >
                                    Successiva
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if(!$coursesOnly && isset($courseUnpaidSummary)): ?>
        <?php $showFutureCourses = !empty($courseUnpaidShowFuture); ?>
        <?php $courseFilterParams = request()->except('show_future_course_payments'); ?>
        <div class="card p-6 space-y-5" x-data="{ expandedCourse: null }">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900">Morosità corsi</h3>
                    <?php if($showFutureCourses): ?>
                        <p class="text-sm text-stone-500">
                            Sono visualizzate tutte le scadenze pendenti: <?php echo e($courseUnpaidSummary['total_unpaid']); ?> (di cui <?php echo e($courseUnpaidSummary['future_total'] ?? 0); ?> future).
                        </p>
                    <?php else: ?>
                        <p class="text-sm text-stone-500">
                            Situazione aggiornata al <?php echo e($courseUnpaidSummary['month_label']); ?>. Totale clienti in ritardo: <?php echo e($courseUnpaidSummary['total_unpaid']); ?>.
                        </p>
                    <?php endif; ?>
                </div>
                <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 text-sm text-stone-600">
                    <?php
                        foreach ($courseFilterParams as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $item) {
                                    echo '<input type="hidden" name="'.e($key).'[]" value="'.e($item).'">';
                                }
                            } else {
                                echo '<input type="hidden" name="'.e($key).'" value="'.e($value).'">';
                            }
                        }
                    ?>
                    <input type="hidden" name="show_future_course_payments" value="0">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="show_future_course_payments"
                            value="1"
                            class="rounded border-stone-300 text-teal-600 focus:ring-teal-500"
                            <?php if($showFutureCourses): echo 'checked'; endif; ?>
                            onchange="this.form.submit()"
                        >
                        <span>Mostra morosità prossimo mese</span>
                    </label>
                </form>
            </div>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $courseUnpaidSummary['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-stone-200 rounded-2xl p-4 bg-white shadow-sm">
                        <button
                            type="button"
                            class="w-full flex items-center justify-between gap-3 text-left"
                            @click="expandedCourse === <?php echo e($summary['course_id']); ?> ? expandedCourse = null : expandedCourse = <?php echo e($summary['course_id']); ?>"
                            <?php if($summary['count'] === 0): echo 'disabled'; endif; ?>
                        >
                            <div>
                                <p class="text-base font-semibold text-stone-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg> <?php echo e($summary['title']); ?></p>
                               
                            </div>
                            <span class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-sm font-semibold
                                <?php echo e($summary['count'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'); ?>">
                                <?php echo e($summary['count']); ?>

                            </span>
                        </button>
                        <?php if($showFutureCourses && (($summary['future_count'] ?? 0) > 0)): ?>
                            <p class="mt-2 text-xs text-amber-600 font-semibold">
                                Include <?php echo e($summary['future_count']); ?> scadenze future.
                            </p>
                        <?php endif; ?>

                        <div
                            class="mt-4 space-y-3"
                            x-show="expandedCourse === <?php echo e($summary['course_id']); ?>"
                            x-cloak
                        >
                            <?php if($summary['count'] === 0): ?>
                                <p class="text-sm text-emerald-600 font-medium">Tutti i clienti sono in regola con il pagamento.</p>
                            <?php else: ?>
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
                                            <?php $__currentLoopData = $summary['unpaid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $whatsapp = preg_replace('/\D+/', '', $entry['client_telephone'] ?? '');
                                                ?>
                                                <tr class="hover:bg-stone-50">
                                                    <td class="px-4 py-3">
                                                        <p class="font-semibold text-stone-800"><?php echo e($entry['client_name'] ?? 'Allieva-o'); ?></p>
                                                        <p class="text-xs text-stone-400">ID pagamento #<?php echo e($entry['payment_id']); ?></p>
                                                    </td>
                                                    <td class="px-4 py-3 space-y-1 text-sm">
                                                        <?php if(!empty($entry['client_email'])): ?>
                                                            <a href="mailto:<?php echo e($entry['client_email']); ?>" class="text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                                                <?php echo e($entry['client_email']); ?>

                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if(!empty($entry['client_telephone'])): ?>
                                                            <div>
                                                                <?php if($whatsapp): ?>
                                                                    <a href="https://wa.me/<?php echo e($whatsapp); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                                                        <?php echo e($entry['client_telephone']); ?>

                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-stone-600"><?php echo e($entry['client_telephone']); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                   <td class="px-4 py-3 text-stone-600">
                                                       <p class="text-sm font-semibold text-stone-700">
                                                           <?php echo e($entry['period_label'] ?? ($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->translatedFormat('F Y') : '—')); ?>

                                                       </p>
                                                       <?php if(!empty($entry['plan_label'])): ?>
                                                           <p class="text-xs text-stone-500">Piano: <?php echo e($entry['plan_label']); ?></p>
                                                       <?php endif; ?>
                                                        <?php if(!empty($entry['is_future'])): ?>
                                                            <span class="inline-flex items-center gap-1 mt-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 uppercase">
                                                                Futuro
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-stone-700 font-semibold">
                                                        € <?php echo e(number_format($entry['amount'] ?? 0, 2, ',', '.')); ?>

                                                    </td>
                                                    <td class="px-4 py-3 text-stone-600">
                                                        <?php echo e($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->format('d/m/Y') : '—'); ?>

                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-row flex-wrap items-center gap-2" x-data="{ showWaiveForm: false }">
                                                            <form
                                                                method="POST"
                                                                action="<?php echo e(route('admin.payments.update', $entry['payment_id'])); ?>"
                                                                class="flex flex-wrap gap-1"
                                                                onsubmit="return confirm('Confermi di registrare il pagamento per <?php echo e($entry['client_name'] ?? 'questa allieva-o'); ?>?');"
                                                            >
                                                                <?php echo csrf_field(); ?>
                                                                <label class="sr-only" for="course-payment-method-<?php echo e($entry['payment_id']); ?>">Metodo</label>
                                                                <select
                                                                    id="course-payment-method-<?php echo e($entry['payment_id']); ?>"
                                                                    name="action"
                                                                    class="input-field text-[11px] py-1 h-8 w-28"
                                                                    x-data
                                                                    @change="const ref = $el.closest('form').querySelector('[data-transfer-reference]'); ref && (ref.classList.toggle('hidden', $el.value !== 'bank_transfer')); if($el.value !== 'bank_transfer' && ref){ ref.value=''; }"
                                                                >
                                                                    <option value="cash">Contanti</option>
                                                                    <option value="bank_transfer">Bonifico</option>
                                                                </select>
                                                                <input type="hidden" name="reason" value="">
                                                                <input type="number" step="0.01" name="amount" placeholder="Importo" class="input-field text-[11px] py-1 h-8 w-24" value="<?php echo e($entry['amount'] ?? ''); ?>">
                                                                <input type="text" name="transfer_reference" placeholder="CRO / Riferimento" class="input-field text-[11px] py-1 h-8 w-32 hidden" data-transfer-reference>
                                                                <input type="text" name="note" placeholder="Nota (opz.)" class="input-field text-[11px] py-1 h-8 w-32">
                                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition-colors hover:bg-teal-700">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                    Registra pagamento
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
                                                                    action="<?php echo e(route('admin.payments.update', $entry['payment_id'])); ?>"
                                                                    class="space-y-2"
                                                                    x-show="showWaiveForm"
                                                                    x-cloak
                                                                    onsubmit="return confirm('Confermi di annullare il mese per <?php echo e($entry['client_name'] ?? 'questo allieva/o'); ?>?');"
                                                                >
                                                                    <?php echo csrf_field(); ?>
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
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-stone-500">Nessun corso registrato al momento.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</section>
<?php /**PATH C:\yoga-studio-erp\resources\views/dashboard/partials/admin.blade.php ENDPATH**/ ?>