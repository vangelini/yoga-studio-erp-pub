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
        membershipPanelOpen: <?php echo json_encode(request()->has('membership_page'), 15, 512) ?>,
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
                <div class="flex flex-wrap items-center gap-3">
                    <?php if($showSettings): ?>
                    <a href="<?php echo e(route('admin.settings.edit')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 3.75a1.5 1.5 0 013 0V5a1.5 1.5 0 01-3 0V3.75zM5.636 5.636a1.5 1.5 0 010 2.121l-.884.884a1.5 1.5 0 01-2.122-2.121l.884-.884a1.5 1.5 0 012.122 0zM3.75 10.5H5a1.5 1.5 0 010 3H3.75a1.5 1.5 0 010-3zM5.636 18.364a1.5 1.5 0 01-2.122 0l-.884-.884a1.5 1.5 0 112.122-2.121l.884.884a1.5 1.5 0 000 2.121zM10.5 18.75V20a1.5 1.5 0 003 0v-1.25a1.5 1.5 0 00-3 0zM18.364 18.364a1.5 1.5 0 002.122 0l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 000 2.121zM20.25 13.5H19a1.5 1.5 0 110-3h1.25a1.5 1.5 0 110 3zM18.364 5.636l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 002.122 2.121z"/>
                        </svg>
                        Impostazioni
                    </a>
                    <?php endif; ?>
                    <?php if($showClientAdmin): ?>
                    <a href="<?php echo e(route('admin.clients.index')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 9.75h15m-13.5 3H12m-7.5 3H12m6.75-6v6.75a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 016.75 4.5h9a2.25 2.25 0 012.25 2.25V9.75z" />
                        </svg>
                        Amministrazione allieve/i
                    </a>
                    <?php endif; ?>
                    <?php if(($viewConfig['show_course_unpaid'] ?? false)): ?>
                    <a href="<?php echo e(route('notifications.index')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Centro notifiche
                    </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->role === 'Admin'): ?>
                    <a href="<?php echo e(route('admin.accounting.index')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M10 6h11M3 6h4m-4 12h4m6 0h9" />
                        </svg>
                        Contabilità
                    </a>
                    <?php endif; ?>
                    <?php if($showTeacherAdmin): ?>
                    <a href="<?php echo e(route('admin.teachers.index')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l-3 3m3-3l3 3m-3-3V4m9 5v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9" />
                        </svg>
                        Amministrazione insegnanti
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/30 shadow-inner text-center text-xs uppercase tracking-widest">
                <div class="flex flex-col text-white/80">
                    <span>Clienti</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($clientCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Corsi</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($courseCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>insegnanti</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherCount); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($membershipSummary)): ?>
        <?php
            $membershipCurrentPage = $membershipSummary['current_page'] ?? 1;
            $membershipLastPage = $membershipSummary['last_page'] ?? 1;
        ?>
        <div class="card p-3 space-y-2">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-left transition hover:border-stone-300 hover:bg-stone-100"
                @click="membershipPanelOpen = !membershipPanelOpen"
            >
                <div>
                    <h3 class="text-lg font-semibold text-stone-900">Morosità quota associativa</h3>
                    <p class="text-xs text-stone-500">Totale quote in attesa: <?php echo e($membershipSummary['total']); ?></p>
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
                <?php if($membershipSummary['total'] === 0): ?>
                    <p class="text-sm text-stone-500">Tutti i clienti sono in regola con la quota associativa per l’attuale stagione.</p>
                <?php else: ?>
                    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-500">
                    
                            <span>Mostra <?php echo e($membershipSummary['per_page']); ?> voci per pagina (configurabile dalle impostazioni)</span>
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
                                                    <input type="hidden" name="action" value="cash">
                                                    <input type="hidden" name="reason" value="">
                                                    <input type="number" step="0.01" name="amount" placeholder="Importo"
                                                        class="input-field text-[11px] py-1 h-8" value="<?php echo e($entry['amount'] ?? ''); ?>">
                                                    <input type="text" name="note" placeholder="Nota (opzionale)"
                                                        class="input-field text-[11px] py-1 h-8">
                                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0-2.737.81-2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Paga in contanti
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
                                    href="<?php echo e($membershipCurrentPage > 1 ? request()->fullUrlWithQuery(['membership_page' => $prevPage]) : '#'); ?>"
                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 font-semibold transition <?php echo e($membershipCurrentPage > 1 ? 'text-stone-600 hover:bg-stone-100' : 'cursor-not-allowed text-stone-300'); ?>"
                                    <?php if($membershipCurrentPage <= 1): ?> aria-disabled="true" <?php endif; ?>
                                >
                                    Precedente
                                </a>
                                <nav class="flex items-center gap-1">
                                    <?php $__currentLoopData = $pageNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a
                                            href="<?php echo e($page === $membershipCurrentPage ? '#' : request()->fullUrlWithQuery(['membership_page' => $page])); ?>"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border px-2 text-[11px] font-semibold transition <?php echo e($page === $membershipCurrentPage ? 'border-teal-500 bg-teal-50 text-teal-700 cursor-default' : 'border-stone-300 text-stone-600 hover:bg-stone-100'); ?>"
                                            <?php if($page === $membershipCurrentPage): ?> aria-current="page" <?php endif; ?>
                                        >
                                            <?php echo e($page); ?>

                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </nav>
                                <a
                                    href="<?php echo e($membershipCurrentPage < $membershipLastPage ? request()->fullUrlWithQuery(['membership_page' => $nextPage]) : '#'); ?>"
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
    <?php if(isset($courseUnpaidSummary)): ?>
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
                            Situazione aggiornata per <?php echo e($courseUnpaidSummary['month_label']); ?>. Totale clienti in ritardo: <?php echo e($courseUnpaidSummary['total_unpaid']); ?>.
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
                                <p class="text-base font-semibold text-stone-800"><?php echo e($summary['title']); ?></p>
                                <?php if(!empty($summary['plans'])): ?>
                                    <p class="text-xs text-stone-500 flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $summary['plans']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5">
                                                <?php echo e($plan['label']); ?> · € <?php echo e(number_format($plan['amount'] ?? 0, 2, ',', '.')); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-stone-500">Tariffe non configurate.</p>
                                <?php endif; ?>
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
                                                                onsubmit="return confirm('Confermi di registrare in contanti il pagamento per <?php echo e($entry['client_name'] ?? 'questa allieva-o'); ?>?');"
                                                            >
                                                                <?php echo csrf_field(); ?>
                                                                <input type="hidden" name="action" value="cash">
                                                                <input type="hidden" name="reason" value="">
                                                                <input type="number" step="0.01" name="amount" placeholder="Importo" class="input-field text-[11px] py-1 h-8 w-24" value="<?php echo e($entry['amount'] ?? ''); ?>">
                                                                <input type="text" name="note" placeholder="Nota (opz.)" class="input-field text-[11px] py-1 h-8 w-32">
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

    <?php echo $__env->make('dashboard.partials.admin-courses', [
        'teacherOptions' => $teacherSelectOptions,
        'dayOptions' => $dayOptions,
        'allowCourseCreation' => $allowCourseCreation,
        'allowTeacherSelection' => $allowTeacherSelection,
        'courseCardTitle' => $courseCardTitle,
        'courseCardSubtitle' => $courseCardSubtitle,
        'currentTeacherId' => $courseCardTeacherId,
        'allowStudentManage' => $allowStudentManage,
        'viewMode' => $viewConfig['mode'] ?? 'admin',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</section>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/partials/admin.blade.php ENDPATH**/ ?>