<?php
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
?>

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
        membershipPanelOpen: false,
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
                    <a href="<?php echo e(route('admin.settings.edit')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-xs font-semibold text-white border border-white/40 backdrop-blur-sm hover:bg-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 3.75a1.5 1.5 0 013 0V5a1.5 1.5 0 01-3 0V3.75zM5.636 5.636a1.5 1.5 0 010 2.121l-.884.884a1.5 1.5 0 01-2.122-2.121l.884-.884a1.5 1.5 0 012.122 0zM3.75 10.5H5a1.5 1.5 0 010 3H3.75a1.5 1.5 0 010-3zM5.636 18.364a1.5 1.5 0 01-2.122 0l-.884-.884a1.5 1.5 0 112.122-2.121l.884.884a1.5 1.5 0 000 2.121zM10.5 18.75V20a1.5 1.5 0 003 0v-1.25a1.5 1.5 0 00-3 0zM18.364 18.364a1.5 1.5 0 002.122 0l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 000 2.121zM20.25 13.5H19a1.5 1.5 0 110-3h1.25a1.5 1.5 0 110 3zM18.364 5.636l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 002.122 2.121z"/>
                        </svg>
                        Impostazioni
                    </a>
                    <form method="POST" action="<?php echo e(route('admin.memberships.generate')); ?>" x-ref="generateMembershipForm">
                        <?php echo csrf_field(); ?>
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
                    <span class="text-2xl font-semibold text-white"><?php echo e($clientCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Corsi</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($courseCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Docenti</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherCount); ?></span>
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
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition"
                    @click="$refs.generateMembershipForm.submit(); showGenerateModal = false;"
                >
                    Conferma operazione
                </button>
            </div>
        </div>
    </div>

    <?php if(isset($membershipSummary)): ?>
        <div class="card p-6 space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900">Morosità quota associativa</h3>
                    <p class="text-sm text-stone-500">Totale quote in attesa: <?php echo e($membershipSummary['total']); ?></p>
                </div>
            </div>

            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $membershipSummary['entries']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-stone-200 rounded-2xl p-4 bg-white shadow-sm">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-base font-semibold text-stone-800"><?php echo e($entry['name']); ?></p>
                                <p class="text-xs text-stone-500"><?php echo e($entry['email'] ?? 'Email non disponibile'); ?></p>
                                <?php if($entry['telephone']): ?>
                                    <p class="text-xs text-stone-500"><?php echo e($entry['telephone']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-stone-600">
                                <p>Importo: <strong>€ <?php echo e(number_format($entry['amount'] ?? 0, 2, ',', '.')); ?></strong></p>
                                <p>Scadenza: <strong><?php echo e($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->format('d/m/Y') : '—'); ?></strong></p>
                            </div>
                        </div>

                        <?php if($entry['payment_id']): ?>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                                <form
                                    method="POST"
                                    action="<?php echo e(route('admin.payments.update', $entry['payment_id'])); ?>"
                                    class="flex flex-col gap-2 sm:flex-row sm:items-center"
                                    onsubmit="return confirm('Confermi di registrare la quota associativa per <?php echo e($entry['name']); ?>?');"
                                >
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="cash">
                                    <input type="hidden" name="reason" value="">
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Segna contanti
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-stone-500">Tutti i clienti sono in regola con la quota associativa.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($courseUnpaidSummary)): ?>
        <div class="card p-6 space-y-5" x-data="{ expandedCourse: null }">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-stone-900">Morosità classi mensili</h3>
                    <p class="text-sm text-stone-500">
                        Situazione aggiornata per <?php echo e($courseUnpaidSummary['month_label']); ?>. Totale clienti in ritardo: <?php echo e($courseUnpaidSummary['total_unpaid']); ?>.
                    </p>
                </div>
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
                                <p class="text-xs text-stone-500">Quota mensile: € <?php echo e(number_format($summary['price'] ?? 0, 2, ',', '.')); ?></p>
                            </div>
                            <span class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-sm font-semibold
                                <?php echo e($summary['count'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'); ?>">
                                <?php echo e($summary['count']); ?>

                            </span>
                        </button>

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
                                                <th class="px-4 py-3 text-left font-semibold">Cliente</th>
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
                                                        <p class="font-semibold text-stone-800"><?php echo e($entry['client_name'] ?? 'Cliente'); ?></p>
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
                                                        <?php echo e($entry['period_label'] ?? ($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->translatedFormat('F Y') : '—')); ?>

                                                    </td>
                                                    <td class="px-4 py-3 text-stone-700 font-semibold">
                                                        € <?php echo e(number_format($entry['amount'] ?? 0, 2, ',', '.')); ?>

                                                    </td>
                                                    <td class="px-4 py-3 text-stone-600">
                                                        <?php echo e($entry['due_date'] ? \Carbon\Carbon::parse($entry['due_date'])->format('d/m/Y') : '—'); ?>

                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col gap-2" x-data="{ showWaiveForm: false }">
                                                            <form
                                                                method="POST"
                                                                action="<?php echo e(route('admin.payments.update', $entry['payment_id'])); ?>"
                                                                class="flex flex-col gap-2"
                                                                onsubmit="return confirm('Confermi di registrare in contanti il pagamento per <?php echo e($entry['client_name'] ?? 'questo cliente'); ?>?');"
                                                            >
                                                                <?php echo csrf_field(); ?>
                                                                <input type="hidden" name="action" value="cash">
                                                                <input type="hidden" name="reason" value="">
                                                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-teal-700">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                    Paga in contanti
                                                                </button>
                                                            </form>
                                                            <div>
                                                                <button
                                                                    type="button"
                                                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-colors hover:bg-stone-300"
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
                                                                    class="mt-2 space-y-2"
                                                                    x-show="showWaiveForm"
                                                                    x-cloak
                                                                    onsubmit="return confirm('Confermi di annullare il mese per <?php echo e($entry['client_name'] ?? 'questo cliente'); ?>?');"
                                                                >
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="action" value="waive">
                                                                    <textarea name="reason" rows="2" class="input-field text-xs" placeholder="Motivo (es. malattia)" required></textarea>
                                                                    <div class="flex items-center gap-2">
                                                                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-teal-700">
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

    <?php echo $__env->make('dashboard.partials.admin-courses', ['teacherOptions' => $teacherSelectOptions, 'dayOptions' => $dayOptions], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-stone-900">Clienti</h3>
                <p class="text-sm text-stone-500">Gestisci dati anagrafici, stato account e pagamenti delle quote associative.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="<?php echo e(route('admin.users.export')); ?>"
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
            action="<?php echo e(route('admin.users.store')); ?>"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6"
        >
            <?php echo csrf_field(); ?>
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
                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['code']); ?>" <?php if($option['code'] === '+39'): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $phoneParts = explode(' ', $client->telephone ?? '', 2);
                            $clientPrefix = $phoneParts[0] ?? '+39';
                            $clientNumber = $phoneParts[1] ?? '';
                            $clientWhatsapp = preg_replace('/\D+/', '', $client->telephone ?? '');
                        ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800"><?php echo e($client->name); ?></td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($client->email): ?>
                                    <a href="mailto:<?php echo e($client->email); ?>" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted"><?php echo e($client->email); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($client->telephone && $clientWhatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($clientWhatsapp); ?>" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        <?php echo e($client->telephone); ?>

                                    </a>
                                <?php else: ?>
                                    <?php echo e($client->telephone ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($client->status === 'active'): ?> bg-emerald-100 text-emerald-700
                                    <?php elseif($client->status === 'pending'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-rose-100 text-rose-700 <?php endif; ?>">
                                    <?php echo e(ucfirst($client->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleClient(<?php echo e($client->id); ?>)">
                                    <span x-text="expandedClient === <?php echo e($client->id); ?> ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedClient === <?php echo e($client->id); ?>" x-cloak x-transition>
                            <td colspan="5" class="px-4 pb-5">
                                <div class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <form method="POST" action="<?php echo e(route('admin.users.profile', $client)); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                                                <input type="text" name="first_name" value="<?php echo e($client->first_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                                                <input type="text" name="last_name" value="<?php echo e($client->last_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                                                <input type="email" name="email" value="<?php echo e($client->email); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                                                <select name="telephone_country" class="input-field text-sm">
                                                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($option['code']); ?>" <?php if($clientPrefix === $option['code']): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                                                <input type="text" name="telephone" value="<?php echo e($clientNumber); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                                                <input type="text" name="residenza_citta" value="<?php echo e($client->residenza_citta); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                                                <input type="text" name="residenza_provincia" value="<?php echo e($client->residenza_provincia); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                                                <input type="text" name="residenza_stato" value="<?php echo e($client->residenza_stato); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                                                <input type="text" name="residenza_via" value="<?php echo e($client->residenza_via); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                                                <input type="text" name="residenza_numero_civico" value="<?php echo e($client->residenza_numero_civico); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                                                <input type="text" name="codice_fiscale" value="<?php echo e($client->codice_fiscale); ?>" required maxlength="16" pattern="[A-Za-z0-9]{16}" oninput="this.value = this.value.toUpperCase()" class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                                                <input type="text" name="luogo_nascita" value="<?php echo e($client->luogo_nascita); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                                                <input type="date" name="data_nascita" value="<?php echo e(optional($client->data_nascita)->format('Y-m-d')); ?>" required class="input-field text-sm">
                                            </div>
                                            <div class="md:col-span-2 flex justify-end">
                                                <button type="submit" class="btn-primary text-sm">Salva dati</button>
                                            </div>
                                        </form>

                                        <div class="space-y-4">
                                            <form method="POST" action="<?php echo e(route('admin.users.update', $client)); ?>" class="flex flex-col md:flex-row md:items-center md:gap-2">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="hidden" name="role" value="Client">
                                                <select name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                                    <?php $__currentLoopData = ['active', 'pending', 'disabled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($statusOption); ?>" <?php if($client->status === $statusOption): echo 'selected'; endif; ?>><?php echo e(ucfirst($statusOption)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button type="submit" class="bg-teal-600 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">Aggiorna stato</button>
                                            </form>

                                            <div class="flex flex-wrap items-center gap-2 text-xs text-stone-600">
                                                <span class="font-semibold text-stone-700 uppercase tracking-wide">Email verificata:</span>
                                                <?php if($client->email_verified_at): ?>
                                                    <span class="text-emerald-600 font-semibold">Sì (<?php echo e(optional($client->email_verified_at)->format('d/m/Y H:i')); ?>)</span>
                                                <?php else: ?>
                                                    <span class="text-amber-600 font-semibold">No</span>
                                                    <form method="POST" action="<?php echo e(route('admin.users.resendVerification', $client)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-xs bg-amber-500 text-white font-semibold px-3 py-2 rounded-lg hover:bg-amber-600 transition-colors">Reinvia email</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <form method="POST" action="<?php echo e(route('admin.users.passwordEmail', $client)); ?>" class="flex items-center gap-3">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="text-xs bg-rose-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-rose-600 transition-colors">Invia reset password</button>
                                                <span class="text-xs text-stone-500">L'utente riceverà un link per scegliere una nuova password.</span>
                                            </form>
                                            <?php if($client->current_membership): ?>
                                                <div class="border border-stone-200 rounded-lg px-4 py-3 bg-white space-y-2 text-xs text-stone-600">
                                                    <p class="font-semibold text-stone-700 uppercase tracking-wide">Quota <?php echo e($client->current_membership->season_start_year); ?>/<?php echo e($client->current_membership->season_start_year + 1); ?></p>
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <span>Scadenza: <strong><?php echo e(optional($client->current_membership->due_date)->format('d/m/Y') ?? '—'); ?></strong></span>
                                                        <span>Importo: <strong>€ <?php echo e(number_format($client->current_membership->amount ?? 0, 2, ',', '.')); ?></strong></span>
                                                        <span>Stato: <strong><?php echo e(ucfirst($client->current_membership->status)); ?></strong></span>
                                                        <span>Pagato il: <strong><?php echo e(optional($client->current_membership->paid_at)->format('d/m/Y H:i') ?? '—'); ?></strong></span>
                                                    </div>
                                                    <?php if(optional($client->membership_payment)?->receipt_url): ?>
                                                        <div class="mt-2">
                                                            <a href="<?php echo e(route('admin.payments.receipt', optional($client->membership_payment)->id)); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                                                                </svg>
                                                                Scarica ricevuta
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if(optional($client->membership_payment)->status !== 'paid' && $client->membership_payment): ?>
                                                        <form method="POST" action="<?php echo e(route('admin.payments.update', $client->membership_payment)); ?>" class="flex justify-end">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="cash">
                                                            <input type="hidden" name="reason" value="">
                                                            <button type="submit" class="text-xs bg-teal-600 text-white font-semibold px-3 py-2 rounded-lg hover:bg-teal-700 transition-colors">Paga in contanti</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($client->payments->isNotEmpty()): ?>
                                                <div class="border border-stone-200 rounded-lg px-4 py-3 bg-white">
                                                    <p class="text-xs uppercase text-stone-500 font-semibold mb-2">Ultimi pagamenti</p>
                                                    <ul class="space-y-2 text-xs text-stone-600">
                                                        <?php $__currentLoopData = $client->payments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li class="rounded-lg border border-stone-200 bg-stone-50 px-3 py-2" x-data="{ showWaiveForm: false }">
                                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                                    <span><?php echo e(ucfirst($payment->type)); ?> · <?php echo e(optional($payment->due_date)->format('d/m/Y') ?? '—'); ?></span>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="font-semibold <?php echo e($payment->status === 'paid' ? 'text-emerald-600' : ($payment->status === 'waived' ? 'text-sky-600' : 'text-amber-600')); ?>">
                                                                            <?php echo e($payment->status === 'paid' ? 'Pagato' : ($payment->status === 'waived' ? 'Annullato' : 'In attesa')); ?>

                                                                        </span>
                                                                        <?php if($payment->receipt_url): ?>
                                                                            <a href="<?php echo e(route('admin.payments.receipt', $payment->id)); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-2 py-1 text-[10px] font-semibold text-white hover:bg-teal-700">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                                                                                </svg>
                                                                                Ricevuta
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <?php if($payment->status === 'pending'): ?>
                                                                    <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                                                        <form
                                                                            method="POST"
                                                                            action="<?php echo e(route('admin.payments.update', $payment->id)); ?>"
                                                                            class="flex flex-col gap-2 sm:flex-row sm:items-center"
                                                                            onsubmit="return confirm('Confermi di registrare in contanti questo pagamento?');"
                                                                        >
                                                                            <?php echo csrf_field(); ?>
                                                                            <input type="hidden" name="action" value="cash">
                                                                            <input type="hidden" name="reason" value="">
                                                                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0 2.737.81 2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                </svg>
                                                                                Paga in contanti
                                                                            </button>
                                                                        </form>
                                                                        <?php if($payment->type === 'course_subscription'): ?>
                                                                            <div class="flex-1">
                                                                                <button
                                                                                    type="button"
                                                                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 hover:bg-stone-300 transition-colors"
                                                                                    @click="showWaiveForm = !showWaiveForm"
                                                                                >
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                    </svg>
                                                                                    Annulla mese
                                                                                </button>
                                                                                <form
                                                                                    method="POST"
                                                                                    action="<?php echo e(route('admin.payments.update', $payment->id)); ?>"
                                                                                    class="mt-2 space-y-2"
                                                                                    x-show="showWaiveForm"
                                                                                    x-cloak
                                                                                    onsubmit="return confirm('Confermi di annullare il mese per questo cliente?');"
                                                                                >
                                                                                    <?php echo csrf_field(); ?>
                                                                                    <input type="hidden" name="action" value="waive">
                                                                                    <textarea name="reason" rows="2" class="input-field text-xs" placeholder="Motivo (es. malattia)" required></textarea>
                                                                                    <div class="flex items-center gap-2">
                                                                                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                            Conferma annulla
                                                                                        </button>
                                                                                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-200" @click="showWaiveForm = false">
                                                                                            Annulla
                                                                                        </button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Nessun cliente registrato al momento.</td>
                        </tr>
                    <?php endif; ?>
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
            action="<?php echo e(route('admin.users.store')); ?>"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6 mb-4"
        >
            <?php echo csrf_field(); ?>
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
                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['code']); ?>" <?php if($option['code'] === '+39'): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $teacherAdminList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $teacherUser = $teacher->user;
                            $teacherPhoneParts = explode(' ', $teacherUser->telephone ?? '', 2);
                            $teacherPrefix = $teacherPhoneParts[0] ?? '+39';
                            $teacherNumber = $teacherPhoneParts[1] ?? '';
                            $assignedCourses = $teacher->courses->pluck('id')->all();
                            $teacherWhatsapp = preg_replace('/\D+/', '', $teacherUser->telephone ?? '');
                        ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800"><?php echo e($teacherUser->name); ?></td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($teacherUser->email): ?>
                                    <a href="mailto:<?php echo e($teacherUser->email); ?>" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted"><?php echo e($teacherUser->email); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($teacherUser->telephone && $teacherWhatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($teacherWhatsapp); ?>" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        <?php echo e($teacherUser->telephone); ?>

                                    </a>
                                <?php else: ?>
                                    <?php echo e($teacherUser->telephone ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($teacherUser->status === 'active'): ?> bg-emerald-100 text-emerald-700
                                    <?php elseif($teacherUser->status === 'pending'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-rose-100 text-rose-700 <?php endif; ?>">
                                    <?php echo e(ucfirst($teacherUser->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php echo $teacher->can_host_private
                                    ? '<span class="text-emerald-600 font-semibold">Abilitate</span>'
                                    : '<span class="text-stone-500">Disabilitate</span>'; ?>

                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleTeacher(<?php echo e($teacher->id); ?>)">
                                    <span x-text="expandedTeacher === <?php echo e($teacher->id); ?> ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedTeacher === <?php echo e($teacher->id); ?>" x-cloak x-transition>
                            <td colspan="6" class="px-4 pb-5">
                                <div class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <form method="POST" action="<?php echo e(route('admin.users.profile', $teacherUser)); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                                                <input type="text" name="first_name" value="<?php echo e($teacherUser->first_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                                                <input type="text" name="last_name" value="<?php echo e($teacherUser->last_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                                                <input type="email" name="email" value="<?php echo e($teacherUser->email); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                                                <select name="telephone_country" class="input-field text-sm">
                                                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($option['code']); ?>" <?php if($teacherPrefix === $option['code']): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                                                <input type="text" name="telephone" value="<?php echo e($teacherNumber); ?>" required class="input-field text-sm">
                                            </div>
                                            <div class="md:col-span-2 flex justify-end">
                                                <button type="submit" class="btn-primary text-sm">Salva dati</button>
                                            </div>
                                        </form>

                                        <div class="space-y-4">
                                            <form method="POST" action="<?php echo e(route('admin.users.update', $teacherUser)); ?>" class="flex flex-col md:flex-row md:items-center md:gap-2">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="hidden" name="role" value="Teacher">
                                                <select name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                                    <?php $__currentLoopData = ['active', 'pending', 'disabled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($statusOption); ?>" <?php if($teacherUser->status === $statusOption): echo 'selected'; endif; ?>><?php echo e(ucfirst($statusOption)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button type="submit" class="bg-teal-600 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">Aggiorna stato</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('admin.users.passwordEmail', $teacherUser)); ?>" class="flex items-center gap-3">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="text-xs bg-rose-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-rose-600 transition-colors">Invia reset password</button>
                                            </form>

                                            <div class="flex flex-wrap items-center gap-2 text-xs text-stone-600">
                                                <span class="font-semibold text-stone-700 uppercase tracking-wide">Email verificata:</span>
                                                <?php if($teacherUser->email_verified_at): ?>
                                                    <span class="text-emerald-600 font-semibold">Sì (<?php echo e(optional($teacherUser->email_verified_at)->format('d/m/Y H:i')); ?>)</span>
                                                <?php else: ?>
                                                    <span class="text-amber-600 font-semibold">No</span>
                                                    <form method="POST" action="<?php echo e(route('admin.users.resendVerification', $teacherUser)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-xs bg-amber-500 text-white font-semibold px-3 py-2 rounded-lg hover:bg-amber-600 transition-colors">Reinvia email</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <form method="POST" action="<?php echo e(route('admin.teachers.private', $teacher)); ?>" class="flex items-center gap-3">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="can_host_private" value="0">
                                                <label class="flex items-center gap-2 text-xs text-stone-600">
                                                    <input type="checkbox" name="can_host_private" value="1" <?php if($teacher->can_host_private): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                    Abilita lezioni private
                                                </label>
                                                <button type="submit" class="text-xs bg-teal-600 text-white font-semibold px-3 py-2 rounded-lg hover:bg-teal-700 transition-colors">Aggiorna</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('admin.teachers.courses', $teacher)); ?>" class="space-y-2">
                                                <?php echo csrf_field(); ?>
                                                <p class="text-xs uppercase text-stone-500 font-semibold">Assegna corsi</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-stone-200 rounded-lg p-3 bg-white text-xs">
                                                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <label class="flex items-center gap-2">
                                                            <input type="checkbox" name="course_ids[]" value="<?php echo e($course['id']); ?>" <?php if(in_array($course['id'], $assignedCourses)): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                            <span><?php echo e($course['title']); ?></span>
                                                        </label>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                                <button type="submit" class="btn-primary text-xs">Salva assegnazioni</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Nessun docente registrato al momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php /**PATH /Users/vincenzo/Documents/shanti-sadhana-yoga-center/php-laravel/resources/views/dashboard/partials/admin.blade.php ENDPATH**/ ?>