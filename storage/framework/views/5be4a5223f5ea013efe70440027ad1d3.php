<?php
    use Illuminate\Support\Js;

    $documents = $documents ?? collect();

    $documentDefinitions = [
        'id_front' => [
            'label' => "Carta d'identità - fronte",
            'description' => 'Carica una scansione leggibile del fronte della carta di identità o passaporto.',
        ],
        'id_back' => [
            'label' => "Carta d'identità - retro",
            'description' => 'Assicurati che i dati siano visibili e non tagliati.',
        ],
        'health_card' => [
            'label' => 'Tessera sanitaria',
            'description' => 'Foto o PDF della tessera sanitaria in corso di validità.',
        ],
        'medical_certificate' => [
            'label' => 'Certificato medico',
            'description' => 'Certificato per attività sportiva non agonistica valido per l’anno in corso.',
        ],
    ];

    $clientDashboardPayload = [
        'clientId' => auth()->id(),
        'courses' => $courses,
        'teachers' => $teachers,
        'bookings' => $bookings ?? collect(),
        'subscriptions' => $subscriptions ?? collect(),
        'membership' => $membership ?? null,
        'membershipPayment' => $membership_payment ?? null,
        'payments' => $payments ?? collect(),
        'documents' => $documents,
        'extraDay' => [
            'enabled' => $extra_day_config['enabled'] ?? false,
            'candidateCourseIds' => $extra_day_config['candidateCourseIds'] ?? [],
        ],
        'routes' => [
            'book' => route('client.bookings.store'),
            'cancelBase' => url('/client/bookings'),
            'subscribe' => route('client.subscriptions.store'),
            'cancelSubscription' => url('/client/subscriptions'),
            'paymentMarkBase' => url('/client/payments'),
        ],
        'flash' => [
            'status' => session('status'),
        ],
    ];
?>

<section
    x-data="clientDashboard(<?php echo e(Js::from($clientDashboardPayload)); ?>)"
    x-init="init()"
    class="space-y-10"
>
    <template x-if="statusMessage">
        <div class="rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 px-4 py-3" x-text="statusMessage"></div>
    </template>
    <template x-if="errorMessage">
        <div class="rounded-lg border border-rose-300 bg-rose-50 text-rose-700 px-4 py-3" x-text="errorMessage"></div>
    </template>

    <div class="relative overflow-hidden rounded-2xl border border-teal-200/40 bg-gradient-to-r from-teal-600 via-teal-500 to-emerald-500 text-white shadow-lg">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/honeycomb.png')] opacity-20 pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Benvenuta/o nel tua area personale</p>
                <h2 class="text-3xl md:text-4xl font-semibold">Troverai le tue iscrizioni e i tuoi dati di gestione dei corsi</h2>
            </div>
            <div class="flex items-center gap-4 bg-white/15 backdrop-blur-sm rounded-2xl px-5 py-4 border border-white/30 shadow-inner">
                <div class="flex flex-col text-center">
                    <span class="text-xs uppercase tracking-widest text-white/70">Corsi Attivi</span>
                    <span class="text-2xl font-semibold" x-text="courses.length"></span>
                </div>
                <span class="w-px h-10 bg-white/30"></span>
                    <div class="flex flex-col text-center">
                        <span class="text-xs uppercase tracking-widest text-white/70">Upcoming Lessons</span>
                        <span class="text-2xl font-semibold" x-text="upcomingBookings.length"></span>
                    </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
        <div class="card p-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400 font-semibold">Quota associativa</p>
                    <h3 class="text-2xl font-semibold text-stone-900">Stagione <span x-text="membershipSeasonLabel()"></span></h3>
                    <p class="text-sm text-stone-500" x-text="membershipDueLabel()"></p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full" :class="membershipStatusClass()" x-text="membershipStatusLabel()"></span>
                    <span class="text-lg font-semibold text-stone-700" x-text="formatMoney(membership?.amount ?? 0)"></span>
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-xs text-stone-500">
                    <p class="font-semibold text-stone-600 uppercase tracking-wide">Validità</p>
                    <p>
                        <span x-text="membership?.starts_at ?? '—'"></span>
                        &nbsp;→&nbsp;
                        <span x-text="membership?.ends_at ?? '—'"></span>
                    </p>
                </div>
                <template x-if="membershipPayment && membershipPayment.status !== 'paid'">
                    <span class="text-sm text-rose-600 font-semibold">Pagamento in attesa</span>
                </template>
                <template x-if="membershipPayment && membershipPayment.status === 'paid'">
                    <span class="text-sm text-emerald-600 font-semibold">
                        Pagata il <span x-text="formatDateString(membershipPayment.paid_at)"></span>
                    </span>
                </template>
                <template x-if="membershipPayment && membershipPayment.receipt_route">
                    <a
                        :href="membershipPayment.receipt_route"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 rounded-lg border border-teal-200 px-3 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-50 transition"
                    >
                        Scarica ricevuta
                    </a>
                </template>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-stone-900">Documenti personali</h3>
                <span class="text-xs text-stone-400 uppercase tracking-wide">Obbligatori</span>
            </div>
            <div class="text-xs text-stone-500">Carica PDF o immagini (max 5 MB). La sostituzione è immediata.</div>

            <div class="flex flex-wrap items-center gap-2">
                <?php $__currentLoopData = $documentDefinitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $definition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-50 px-3 py-1.5">
                        <span class="text-xs font-semibold text-stone-700"><?php echo e($definition['label']); ?></span>
                        <template x-if="documentByType('<?php echo e($type); ?>')">
                            <span class="inline-flex items-center gap-1 text-[11px] rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5">
                                Pronto
                            </span>
                        </template>
                        <template x-if="!documentByType('<?php echo e($type); ?>')">
                            <span class="inline-flex items-center gap-1 text-[11px] rounded-full bg-rose-100 text-rose-700 px-2 py-0.5">
                                Mancante
                            </span>
                        </template>

                        <template x-if="documentByType('<?php echo e($type); ?>')">
                            <a
                                :href="documentByType('<?php echo e($type); ?>').url"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-600 hover:text-teal-700 underline decoration-dotted"
                                title="Scarica documento"
                            >
                                Scarica
                            </a>
                        </template>

                        <form method="POST" action="<?php echo e(route('client.documents.store')); ?>" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="document_type" value="<?php echo e($type); ?>">
                            <label class="inline-flex items-center gap-1 cursor-pointer text-[11px] font-semibold text-stone-600 hover:text-stone-800">
                                <input type="file" name="document_file" accept="image/*,application/pdf" required class="sr-only" onchange="this.form.submit()">
                                <span class="inline-flex items-center gap-1 rounded-md bg-stone-200 px-2 py-0.5">Carica</span>
                            </label>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="flex justify-end mt-4">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
            @click="togglePayments"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span x-text="showPayments ? 'Nascondi storico pagamenti' : 'Mostra storico pagamenti'"></span>
        </button>
    </div>

    <div class="card p-6 space-y-4 mt-4" x-show="showPayments" x-cloak>
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-stone-900">Storico pagamenti</h3>
            <span class="text-xs text-stone-400 uppercase tracking-wide">Ultimi movimenti</span>
        </div>
        <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
            <template x-for="payment in payments" :key="payment.id">
                <div class="border border-stone-200 rounded-xl px-4 py-3 bg-stone-50 flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-stone-800" x-text="payment.type === 'membership' ? 'Quota associativa' : (payment.type === 'course_subscription' ? 'Iscrizione corso' : 'Lezione privata')"></span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full" :class="paymentStatusClass(payment.status)" x-text="payment.status === 'paid' ? 'Pagato' : 'In attesa'"></span>
                    </div>
                    <template x-if="payment.is_course_payment">
                        <div class="text-[11px] text-stone-500 space-y-1">
                            <p>
                                <span class="font-semibold text-stone-600">Corso:</span>
                                <span x-text="payment.course_title ?? '—'"></span>
                            </p>
                            <p x-show="payment.plan_label">
                                <span class="font-semibold text-stone-600">Tipo abbonamento:</span>
                                <span x-text="payment.plan_label"></span>
                            </p>
                            <p x-show="payment.subscriptionStartDateDisplay">
                                <span class="font-semibold text-stone-600">Data inizio:</span>
                                <span x-text="payment.subscriptionStartDateDisplay"></span>
                            </p>
                            <p x-show="payment.has_extra_day">
                                <span class="font-semibold text-stone-600">Modalità “Un giorno in più”:</span>
                                <span x-text="payment.extra_day?.course_title ?? 'Aggiunta'"></span>
                            </p>
                        </div>
                    </template>
                    <div class="text-xs text-stone-500 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span x-text="payment.due_date ? `Scadenza ${formatDateString(payment.due_date)}` : ''"></span>
                            <template x-if="payment.receipt_route">
                                <a
                                    :href="payment.receipt_route"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-600 hover:text-teal-700 underline decoration-dotted"
                                >
                                    Scarica ricevuta
                                </a>
                            </template>
                        </div>
                        <span class="font-semibold text-stone-700 text-right" x-text="formatMoney(payment.amount)"></span>
                    </div>
                </div>
            </template>
            <template x-if="payments.length === 0">
                <p class="text-sm text-stone-500">Non hai ancora registrato alcun pagamento.</p>
            </template>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 md:p-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-2xl font-semibold text-stone-900">Corsi Yoga</h3>
                <p class="text-sm text-stone-500">Sfoglia i corsi disponibili e conferma le tue iscrizioni mensili.</p>
            </div>
            <div class="inline-flex items-center gap-2 bg-stone-100 border border-stone-200 rounded-full px-4 py-2 text-xs text-stone-500">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Aggiornato settimanalmente
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <template x-for="course in courses" :key="course.id">
                <div class="p-5 rounded-xl border border-stone-200/70 bg-gradient-to-br from-stone-50 via-white to-white shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold text-teal-700" x-text="course.title"></p>
                            <p class="text-sm text-stone-500">
                                Insegnante: <span x-text="course.teacher_name ?? 'Da assegnare'"></span>
                            </p>
 
                        </div>
                        <div class="text-right">
                            <template x-if="course.availablePlans?.length">
                                <div>
                                    <p class="text-sm text-stone-500">Piano base</p>
                                    <p class="text-xl font-semibold text-stone-800">
                                        €<span x-text="Number(course.availablePlans[0]?.amount ?? 0).toFixed(2)"></span>
                                    </p>
                                    <p class="text-[11px] uppercase text-stone-400" x-text="course.availablePlans[0]?.label"></p>
                                </div>
                            </template>
                            <template x-if="!(course.availablePlans?.length)">
                                <div>
                                    <p class="text-sm text-stone-500">Piani disponibili</p>
                                    <p class="text-xs font-semibold text-rose-500">Non impostati</p>
                                </div>
                            </template>
                        </div>
                    </div>
                    <p class="text-sm text-stone-600 mt-3" x-text="truncate(course.description, 220)"></p>
                    <template x-if="course.start_date_human || course.startDateHuman || course.start_date || course.startDate">
                        <p class="text-xs text-stone-500 mt-2">
                            <span class="font-semibold text-stone-600">Periodo:</span>
                            <span x-text="(course.start_date_human || course.startDateHuman || course.start_date || course.startDate || '—') + ' → ' + (course.end_date_human || course.endDateHuman || course.end_date || course.endDate || '—')"></span>
                        </p>
                    </template>
                    <div class="mt-3 text-xs text-stone-500">
                        <p class="font-semibold text-stone-600 uppercase tracking-wide">Orari settimanali</p>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <template x-for="slot in course.schedule" :key="slot.day + (slot.time ?? 'TBD')">
                                <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-700 font-semibold">
                                    <span x-text="slot.day"></span>
                                    &nbsp;·&nbsp;
                                    <span x-text="slot.time ?? 'TBD'"></span>
                                </span>
                            </template>
                        </div>
                        <template x-if="subscriptionByCourse(course.id)?.start_date || subscriptionByCourse(course.id)?.startDate">
                            <p class="mt-2 text-stone-500">
                                <span class="font-semibold text-stone-600">Iscrizione attiva dal:</span>
                                <span x-text="subscriptionByCourse(course.id)?.startDateDisplay || subscriptionByCourse(course.id)?.start_date || subscriptionByCourse(course.id)?.startDate"></span>
                            </p>
                        </template>
                        <template x-if="subscriptionByCourse(course.id)?.planLabel">
                            <p class="mt-1 text-stone-500">
                                <span class="font-semibold text-stone-600">Piano:</span>
                                <span x-text="subscriptionByCourse(course.id)?.planLabel"></span>
                            </p>
                        </template>
                        <template x-if="subscriptionByCourse(course.id)?.hasExtraDay">
                            <p class="mt-1 text-[11px] font-semibold text-emerald-600">
                                Include l'opzione “Un giorno in più” del corso
                                <span
                                    x-text="subscriptionByCourse(course.id)?.extra_course?.title
                                        ?? subscriptionByCourse(course.id)?.extraCourseSnapshot?.course_title
                                        ?? subscriptionByCourse(course.id)?.extra_course_snapshot?.course_title
                                        ?? 'altro corso'">
                                </span>
                            </p>
                        </template>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <template x-if="!isSubscribed(course.id)">
                            <button
                                type="button"
                                class="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-400 disabled:cursor-not-allowed"
                                @click="openSubscriptionModal(course)"
                                :disabled="loading || !(course.availablePlans?.length)"
                            >
                                Iscriviti
                            </button>
                        </template>
                        <template x-if="isSubscribed(course.id)">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-stone-500">
                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-semibold text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Iscrizione attiva
                                </span>
                                <template x-if="canCancelSubscription(course.id)">
                                    <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-white px-3 py-1.5 font-semibold text-rose-600 hover:bg-rose-50 transition-colors disabled:opacity-60"
                                    @click="cancelSubscription(course.id)"
                                    :disabled="loading"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Annulla iscrizione
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <template x-if="course.availablePlans?.length && !isSubscribed(course.id)">
                        <div class="mt-3">
                            <p class="text-[11px] uppercase font-semibold text-stone-500 tracking-wide">Tipi di abbonamento</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="plan in course.availablePlans" :key="plan.type">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                                        <span x-text="plan.label"></span>
                                        <span>€ <span x-text="Number(plan.amount ?? 0).toFixed(2)"></span></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="courses.length === 0">
                <div class="col-span-full">
                    <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-12 text-center text-stone-500">
                        I corsi saranno disponibili a breve. Torna a trovarci!
                    </div>
                </div>
            </template>
        </div>
    </div>

    <?php if($private_lessons_enabled ?? false): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 md:p-8 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-2xl font-semibold text-stone-900">Prenota Lezioni Individuali</h3>
                <p class="text-sm text-stone-500">Seleziona l'insegnante disponibile</p>
            </div>
            <div class="inline-flex items-center gap-3 bg-rose-50 text-rose-600 border border-rose-100 rounded-full px-5 py-2 text-xs font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2h-2V3H7v2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Cancellazione disponibilità fino a 24 ore prima della lezione
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <template x-for="teacher in teachers" :key="teacher.id">
                <div class="p-5 rounded-xl border border-stone-200/70 bg-gradient-to-br from-white via-stone-50 to-white shadow-sm hover:shadow-md transition flex flex-col gap-4">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-teal-100">
                                <img :src="teacher.profile_picture_url" :alt="teacher.name" class="w-full h-full object-cover">
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white"
                                  :class="availableSlots(teacher).length ? 'bg-emerald-500' : 'bg-stone-300'"></span>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-stone-900" x-text="teacher.name"></p>
                            <p class="text-xs text-stone-500" x-text="teacher.bio ?? 'Yoga Teacher'"></p>
                        </div>
                    </div>
                    <div class="text-xs text-stone-500 space-y-1">
                        
                        <div class="flex flex-wrap gap-2"><p class="uppercase tracking-wide font-semibold text-stone-600">Specialitá:
                            <template x-for="tag in (teacher.specializations || []).length ? teacher.specializations : ['TBA']" :key="tag">
                                <span class="px-3 py-1 rounded-full border border-stone-200 bg-white text-stone-600 font-medium">
                                    <span x-text="tag"></span>
                                </span>
                            </template>
                            </p>
                        </div>
                    </div>
                    <div class="text-xs text-stone-500 bg-stone-100 rounded-lg px-3 py-2 border border-stone-200">
                        <span class="font-semibold text-stone-700">Disponibilitá:</span>
                        <span x-text="availableSlots(teacher)[0]?.displayDate ?? 'Nessuna'"></span>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-2 pt-2 border-t border-stone-200 text-xs"
                        x-show="teacher.email || teacher.whatsappUrl"
                        x-cloak
                    >
                        <template x-if="teacher.email">
                            <a
                                :href="teacher.email ? `mailto:${teacher.email}` : '#'"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-teal-200 text-teal-600 font-semibold hover:bg-teal-50 hover:border-teal-300 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4 4m0 0l-4-4m4 4V8m-6 4V7a2 2 0 012-2h8a2 2 0 012 2v5"/>
                                </svg>
                                Email
                            </a>
                        </template>
                        <template x-if="teacher.whatsappUrl">
                            <a
                                :href="teacher.whatsappUrl"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-emerald-200 text-emerald-600 font-semibold hover:bg-emerald-50 hover:border-emerald-300 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.04 2C6.54 2 2 6.3 2 11.64c0 2.98 1.38 5.66 3.57 7.47l-.47 2.89 2.93-.93c1.36.43 2.4.57 3.99.57 5.5 0 10.04-4.3 10.04-9.64S17.54 2 12.04 2Zm0 17.5c-1.38 0-2.46-.2-3.64-.62l-.26-.09-1.74.55.28-1.7-.11-.18c-1.45-1.3-2.83-3.02-2.83-5.82 0-4.36 3.64-7.93 8.14-7.93 4.47 0 8.1 3.57 8.1 7.93 0 4.38-3.63 7.96-7.94 7.96Zm4.34-5.38c-.24-.12-1.43-.71-1.65-.79-.22-.08-.38-.12-.55.12-.16.24-.63.8-.77.96-.14.16-.28.18-.52.06-.24-.12-1.02-.37-1.94-1.18-.72-.63-1.2-1.4-1.34-1.64-.14-.24-.01-.36.1-.48.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.43-.14-.01-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.55.58.24 1.02.38 1.37.49.58.18 1.1.16 1.5.1.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/>
                                </svg>
                                WhatsApp
                            </a>
                        </template>
                    </div>
                    <button
                        type="button"
                        class="mt-auto inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-300 disabled:pointer-events-none"
                        @click="openBookingModal(teacher.id)"
                        :disabled="loading || availableSlots(teacher).length === 0"
                    >
                        <span>Visualizza Calendario</span>
                        <span class="text-xs bg-white/25 px-2 py-0.5 rounded-full" x-text="availableSlots(teacher).length"></span>
                    </button>
                </div>
            </template>
            <template x-if="teachers.length === 0">
                <div class="col-span-full">
                    <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-12 text-center text-stone-500">
                        Disponibilità insegnanti non ancora caricata.
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden border border-stone-200/60">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 md:p-7">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-2xl font-semibold text-stone-900">Classi private prenotate</h3>

            </div>
            <template x-if="upcomingBookings.length === 0">
                <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-500">
                    Non hai lezioni private prenotate al momento.
                </div>
            </template>
            <div class="space-y-4">
                <template x-for="booking in upcomingBookings" :key="booking.id">
                    <div class="p-4 md:p-5 bg-gradient-to-r from-teal-50 via-white to-white rounded-xl border border-stone-200/60 shadow-sm">
                        <p class="text-lg font-semibold text-teal-700" x-text="formatDate(booking)"></p>
                        <p class="text-sm text-stone-600">
                            <span x-text="formatTime(booking)"></span>
                            &nbsp;·&nbsp;
                            con <span x-text="booking.teacher?.name ?? 'Instructor'"></span>
                        </p>
                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <p class="text-xs text-stone-500" x-text="cancelCountdownLabel(booking)"></p>
                            <button
                                type="button"
                                class="text-xs bg-rose-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-rose-600 transition-colors disabled:bg-rose-300 disabled:cursor-not-allowed"
                                @click="cancelBooking(booking.id)"
                                :disabled="!canCancel(booking) || loading"
                            >
                                Cancella Prenotazione
                            </button>
                        </div>
                        <div
                            class="mt-3 flex flex-wrap items-center gap-2 text-xs"
                            x-show="booking.teacher && (booking.teacher.email || booking.teacher.whatsappUrl)"
                            x-cloak
                        >
                            <template x-if="booking.teacher?.email">
                                <a
                                    :href="`mailto:${booking.teacher.email}`"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-teal-200 text-teal-600 font-semibold hover:bg-teal-50 hover:border-teal-300 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4 4m0 0l-4-4m4 4V8m-6 4V7a2 2 0 012-2h8a2 2 0 012 2v5"/>
                                    </svg>
                                    Email
                                </a>
                            </template>
                            <template x-if="booking.teacher?.whatsappUrl">
                                <a
                                    :href="booking.teacher.whatsappUrl"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-emerald-200 text-emerald-600 font-semibold hover:bg-emerald-50 hover:border-emerald-300 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.04 2C6.54 2 2 6.3 2 11.64c0 2.98 1.38 5.66 3.57 7.47l-.47 2.89 2.93-.93c1.36.43 2.4.57 3.99.57 5.5 0 10.04-4.3 10.04-9.64S17.54 2 12.04 2Zm0 17.5c-1.38 0-2.46-.2-3.64-.62l-.26-.09-1.74.55.28-1.7-.11-.18c-1.45-1.3-2.83-3.02-2.83-5.82 0-4.36 3.64-7.93 8.14-7.93 4.47 0 8.1 3.57 8.1 7.93 0 4.38-3.63 7.96-7.94 7.96Zm4.34-5.38c-.24-.12-1.43-.71-1.65-.79-.22-.08-.38-.12-.55.12-.16.24-.63.8-.77.96-.14.16-.28.18-.52.06-.24-.12-1.02-.37-1.94-1.18-.72-.63-1.2-1.4-1.34-1.64-.14-.24-.01-.36.1-.48.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.43-.14-.01-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.55.58.24 1.02.38 1.37.49.58.18 1.1.16 1.5.1.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/>
                                    </svg>
                                    WhatsApp
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

       

    <!-- Booking Modal -->
    <div
        x-cloak
        x-show="bookingModal.open"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 px-4"
        x-transition
        @keydown.escape.window="closeBookingModal"
    >
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden border border-stone-200/60">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-5 border-b border-stone-200 bg-stone-50">
                <div>
                    <h4 class="text-2xl font-semibold text-stone-900">Prenota una leziona individuale</h4>
                    <p class="text-sm text-stone-500 mt-1">
                        Insegnante:
                        <span class="font-semibold text-teal-700" x-text="bookingModal.teacher?.name"></span>
                    </p>
                </div>
                <button
                    type="button"
                    class="text-stone-400 hover:text-stone-600 text-2xl leading-none"
                    @click="closeBookingModal"
                >
                    &times;
                </button>
            </div>
            <div class="px-6 py-5 space-y-5">
                <template x-if="availableSlots(bookingModal.teacher).length === 0">
                    <p class="text-sm text-stone-500">L' insegnante al momento no ha disponibilitá.</p>
                </template>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2">
                    <template x-for="slot in availableSlots(bookingModal.teacher)" :key="slot.id">
                        <button
                            type="button"
                            class="border rounded-xl px-4 py-3 text-left transition-all bg-white/90 backdrop-blur-sm"
                            :class="bookingModal.selectedSlot && bookingModal.selectedSlot.id === slot.id ? 'border-teal-500 shadow-md ring-1 ring-teal-200' : 'border-stone-200 hover:border-teal-300 hover:shadow-sm'"
                            @click="bookingModal.selectedSlot = slot"
                        >
                            <p class="text-sm font-semibold text-teal-700" x-text="slot.displayDate"></p>
                            <p class="text-xs text-stone-500 mt-1">
                                <span x-text="slot.displayTime"></span>
                                &nbsp;·&nbsp;
                                <span x-text="slot.untilLabel"></span>
                            </p>
                        </button>
                    </template>
                </div>

                <div class="flex justify-end items-center gap-3 pt-4 border-t border-stone-200">
                    <button
                        type="button"
                        class="bg-stone-200 text-stone-700 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors"
                        @click="closeBookingModal"
                    >
                        Chiudi
                    </button>
                    <button
                        type="button"
                        class="bg-teal-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-400 disabled:cursor-not-allowed"
                        @click="confirmBooking"
                        :disabled="!bookingModal.selectedSlot || loading"
                    >
                        Prenota lezione individuale
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Subscription Modal -->
    <div
        x-cloak
        x-show="subscriptionModal.open"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 px-4 py-8"
        x-transition
        @keydown.escape.window="closeSubscriptionModal"
    >
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] border border-stone-200/60 flex flex-col">
            <div class="px-6 py-5 border-b border-stone-200 bg-stone-50">
                <h4 class="text-2xl font-semibold text-stone-900">Iscrizione al Corso: <span class="font-semibold text-teal-700" x-text="subscriptionModal.course?.title"></span></h4>
                    

            </div>
            <div class="px-6 py-5 space-y-5 text-sm text-stone-600 overflow-y-auto">
                <template x-if="subscriptionModal.course">
                    <div class="space-y-4">
                        <p x-text="subscriptionModal.course.description"></p>

                        <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <h5 class="text-sm font-semibold text-stone-700">Scegli il piano di abbonamento</h5>
                            <template x-if="coursePlans(subscriptionModal.course).length > 0">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <template x-for="plan in coursePlans(subscriptionModal.course)" :key="plan.type">
                                        <label class="flex items-start gap-2 rounded-lg border border-transparent px-3 py-2 text-sm hover:border-teal-200 hover:bg-white transition">
                                            <input
                                                type="radio"
                                                name="subscription-plan"
                                                class="mt-1 h-4 w-4 text-teal-600 border-stone-300 focus:ring-teal-500"
                                                :value="plan.type"
                                                :checked="subscriptionModal.planType === plan.type"
                                                @change="selectSubscriptionPlan(plan.type)"
                                            >
                                            <span>
                                                <span class="font-semibold text-stone-700" x-text="plan.label"></span>
                                                <span class="block text-xs text-stone-500">
                                                    € <span x-text="Number(plan.amount ?? 0).toFixed(2)"></span>
                                                    · <span x-text="plan.months === 1 ? '1 mese' : `${plan.months} mesi`"></span>
                                                </span>
                                            </span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                            <template x-if="coursePlans(subscriptionModal.course).length === 0">
                                <p class="text-xs text-rose-500">Nessun piano disponibile.</p>
                            </template>

                            <div class="h-px w-full bg-stone-200"></div>

                            <h5 class="text-sm font-semibold text-stone-700">Scegli la data di inizio</h5>
                            <p class="text-xs text-stone-500" x-text="subscriptionModal.supportsProration ? 'Iniziando nel mese corrente il costo viene calcolato sui giorni rimanenti.' : 'Il costo è fisso per l\'intero periodo selezionato.'"></p>

                            <div class="space-y-3">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="flex items-start gap-3 rounded-xl border border-stone-200 bg-white px-3 py-3 text-sm text-stone-600">
                                        <input
                                            type="radio"
                                            name="subscription-start-option"
                                            value="current_month"
                                            class="mt-1 h-4 w-4 text-teal-600 border-stone-300 focus:ring-teal-500"
                                            :checked="subscriptionModal.option === 'current_month'"
                                            @change="handleSubscriptionOptionChange('current_month')"
                                        >
                                        <span class="space-y-2">
                                            <span>
                                                <span class="font-semibold text-stone-700">Inizia questo mese</span>
                                                <span class="block text-xs text-stone-500" x-text="subscriptionModal.supportsProration ? 'Costo proporzionato ai giorni rimanenti.' : 'Il costo verrà applicato per l\'intera durata del piano.'"></span>
                                            </span>
                                            <div class="space-y-2" x-show="subscriptionModal.option === 'current_month'">
                                                <div class="flex flex-col gap-2">
                                                    <input
                                                        type="date"
                                                        class="w-full rounded-lg border border-stone-300 p-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                                                        :min="subscriptionModal.limits.today"
                                                        :max="subscriptionModal.limits.endOfMonth"
                                                        :value="subscriptionModal.startDate"
                                                        @input="handleSubscriptionDateChange($event.target.value)"
                                                    >
                                                    <span class="text-xs text-stone-500">Disponibile fino al <span x-text="formatDateLabel(subscriptionModal.limits.endOfMonth)"></span></span>
                                                </div>
                                                <p class="text-xs text-rose-500" x-show="subscriptionModal.option === 'current_month' && !isValidSubscriptionDate()">Seleziona una data valida nel mese corrente.</p>
                                            </div>
                                        </span>
                                    </label>

                                    <label class="flex items-start gap-3 rounded-xl border border-stone-200 bg-white px-3 py-3 text-sm text-stone-600">
                                        <input
                                            type="radio"
                                            name="subscription-start-option"
                                            value="next_month"
                                            class="mt-1 h-4 w-4 text-teal-600 border-stone-300 focus:ring-teal-500"
                                            :checked="subscriptionModal.option === 'next_month'"
                                            @change="handleSubscriptionOptionChange('next_month')"
                                        >
                                        <span>
                                            <span class="font-semibold text-stone-700">Inizia dal prossimo mese</span>
                                            <span class="block text-xs text-stone-500">Prima lezione il <span x-text="subscriptionModal.nextMonthLabel"></span>. Prezzo intero.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <template x-if="extraDayEnabled()">
                                <div class="rounded-lg border border-stone-200 bg-white px-4 py-3 space-y-3">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-stone-700">Modalità “Un giorno in più”</p>
                                            <p class="text-xs text-stone-500">Aggiungi una lezione settimanale da un altro corso candidato.</p>
                                        </div>
                                        <template x-if="Number(subscriptionModal.previewExtra) > 0">
                                            <span class="text-sm font-semibold text-emerald-600">+ € <span x-text="subscriptionModal.previewExtra"></span></span>
                                        </template>
                                    </div>
                                    <template x-if="extraDayCandidates().length > 0">
                                        <div class="space-y-2">
                                            <select
                                                class="w-full rounded-lg border border-stone-300 p-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                                                x-model="subscriptionModal.selectedExtraCourseId"
                                                @change="handleExtraCourseChange($event.target.value)"
                                            >
                                                <option value="">Nessuna lezione extra</option>
                                                <template x-for="candidate in extraDayCandidates()" :key="candidate.id">
                                                    <option
                                                        :value="candidate.id"
                                                        :disabled="isSubscribed(candidate.id)"
                                                        x-text="candidate.title"
                                                    ></option>
                                                </template>
                                            </select>
                                            <p class="text-xs text-stone-500">Il costo aggiuntivo viene calcolato in base alle lezioni rimanenti del corso selezionato.</p>
                                        </div>
                                    </template>
                                    <template x-if="extraDayCandidates().length === 0">
                                        <p class="text-xs text-stone-500">Al momento non ci sono corsi disponibili come lezione extra.</p>
                                    </template>
                                </div>
                            </template>

                            <div class="flex flex-col gap-4 rounded-lg border border-stone-200 bg-white px-4 py-4 text-sm">
                                <div>
                                    <span class="text-stone-600 block">Importo dovuto ora</span>
                                    <span class="text-[11px] text-stone-500">
                                        Base € <span x-text="subscriptionModal.previewBase"></span>
                                        <template x-if="Number(subscriptionModal.previewExtra) > 0">
                                            <span> · Extra € <span x-text="subscriptionModal.previewExtra"></span></span>
                                        </template>
                                    </span>
                                </div>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <span class="text-lg font-semibold text-teal-700">€ <span x-text="subscriptionModal.preview"></span></span>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                                            @click="closeSubscriptionModal"
                                        >
                                            Annulla
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-teal-700 disabled:bg-stone-300 disabled:text-stone-500 disabled:cursor-not-allowed"
                                            @click="confirmSubscription"
                                            :disabled="!canSubmitSubscription() || loading"
                                        >
                                            Conferma iscrizione
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="!subscriptionModal.course">
                    <p class="text-sm text-stone-500">Nessun corso selezionato.</p>
                </template>
            </div>
            <div class="border-t border-stone-200"></div>
        </div>
    </div>
</section>

<?php if (! $__env->hasRenderedOnce('f18a710d-c62e-4e72-a8df-67c1602b8891')): $__env->markAsRenderedOnce('f18a710d-c62e-4e72-a8df-67c1602b8891'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('clientDashboard', (payload) => {
                    const normalize = (input) => {
                        if (!input) return [];
                        return Array.isArray(input) ? input : Object.values(input);
                    };
                    const sanitizeTelephone = (telephone) => {
                        if (!telephone) return '';
                        return String(telephone).replace(/\D+/g, '');
                    };
                    const whatsappUrl = (telephone) => {
                        const digits = sanitizeTelephone(telephone);
                        return digits ? `https://wa.me/${digits}` : null;
                    };
                    const decorateTeacher = (teacher) => {
                        if (!teacher) return null;
                        return {
                            ...teacher,
                            whatsappUrl: whatsappUrl(teacher.telephone ?? teacher.phone ?? ''),
                        };
                    };
                    const decorateBooking = (booking) => {
                        if (!booking) return null;
                        return {
                            ...booking,
                            teacher: decorateTeacher(booking.teacher || null),
                        };
                    };

                    return {
                        clientId: payload.clientId,
                        courses: normalize(payload.courses).map(course => {
                            const plans = normalize(course.availablePlans ?? course.available_plans ?? []);
                            return {
                                ...course,
                                availablePlans: plans,
                                available_plans: plans,
                            };
                        }),
                        teachers: normalize(payload.teachers)
                            .map(teacher => {
                                const decorated = decorateTeacher(teacher) ?? {};
                                return {
                                    ...decorated,
                                    availability: normalize(teacher?.availability),
                                };
                            })
                            .filter(Boolean),
                        bookings: normalize(payload.bookings)
                            .map(booking => decorateBooking(booking))
                            .filter(Boolean),
                        subscriptions: normalize(payload.subscriptions).map(s => ({ ...s })),
                        membership: payload.membership ?? null,
                        membershipPayment: payload.membershipPayment ?? null,
                        payments: normalize(payload.payments ?? []).map(payment => ({ ...payment })),
                        documents: normalize(payload.documents ?? []),
                        extraDay: {
                            enabled: Boolean(payload.extraDay?.enabled),
                            candidateCourseIds: normalize(payload.extraDay?.candidateCourseIds ?? []),
                        },
                        showPayments: false,
                        routes: payload.routes,
                        statusMessage: payload.flash?.status ?? '',
                        errorMessage: '',
                        loading: false,
                        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                        bookingModal: {
                            open: false,
                            teacherId: null,
                            teacher: null,
                            selectedSlot: null,
                        },
                        subscriptionModal: {
                            open: false,
                            course: null,
                            option: 'current_month',
                            planType: null,
                            supportsProration: true,
                            startDate: null,
                            preview: '0.00',
                            previewBase: '0.00',
                            previewExtra: '0.00',
                            selectedExtraCourseId: null,
                            nextMonthLabel: '',
                            limits: {
                                today: null,
                                endOfMonth: null,
                                nextMonthStart: null,
                            },
                        },

                        init() {
                            this.refreshDerivedCollections();
                            this.payments = this.payments.sort((a, b) => new Date(b.paid_at || b.due_date || 0) - new Date(a.paid_at || a.due_date || 0));
                        },

                        refreshDerivedCollections() {
                            this.bookings = this.bookings
                                .map((booking) => {
                                    const decorated = decorateBooking(booking) ?? booking;
                                    if ((!decorated.teacher || (!decorated.teacher.email && !decorated.teacher.whatsappUrl)) && decorated.teacherId) {
                                        const fallbackTeacher = this.teachers.find(t => t.id === decorated.teacherId);
                                        if (fallbackTeacher) {
                                            decorated.teacher = {
                                                ...decorateTeacher(fallbackTeacher),
                                            };
                                        }
                                    }
                                    return {
                                        ...decorated,
                                        slot: decorated.slot ?? {
                                            date: decorated.date,
                                            time: decorated.time,
                                        },
                                    };
                                });

                            this.subscriptions.sort((a, b) => {
                                if (a.status === b.status) {
                                    const titleA = (a.course?.title ?? '').toLocaleLowerCase();
                                    const titleB = (b.course?.title ?? '').toLocaleLowerCase();
                                    return titleA.localeCompare(titleB);
                                }

                                return a.status === 'active' ? -1 : 1;
                            });
                        },

                        togglePayments() {
                            this.showPayments = !this.showPayments;
                        },

                        documentByType(type) {
                            return this.documents.find(doc => doc.type === type) || null;
                        },

                        truncate(text, limit) {
                            if (!text) return '';
                            return text.length > limit ? `${text.slice(0, limit)}…` : text;
                        },

                        membershipSeasonLabel() {
                            if (!this.membership) return '—';
                            const start = this.membership.season_start_year;
                            return `${start}/${start + 1}`;
                        },

                        isoToday() {
                            const now = new Date();
                            return now.toISOString().slice(0, 10);
                        },

                        isoEndOfMonth() {
                            const now = new Date();
                            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                            return end.toISOString().slice(0, 10);
                        },

                        isoNextMonthStart() {
                            const now = new Date();
                            const next = new Date(now.getFullYear(), now.getMonth() + 1, 1);
                            return next.toISOString().slice(0, 10);
                        },

                        formatDateLabel(isoString) {
                            if (!isoString) return '';
                            const date = new Date(`${isoString}T00:00:00`);
                            if (Number.isNaN(date.getTime())) return isoString;
                            return date.toLocaleDateString('it-IT', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                            });
                        },

                        parseDate(value) {
                            if (!value) return null;
                            const normalized = value.includes('T') ? value : `${value}T00:00:00`;
                            const date = new Date(normalized);
                            return Number.isNaN(date.getTime()) ? null : date;
                        },

                        coursePlans(course) {
                            return normalize(course?.availablePlans ?? course?.available_plans ?? []);
                        },

                        selectedPlan() {
                            return this.coursePlans(this.subscriptionModal.course)
                                .find(plan => plan.type === this.subscriptionModal.planType) ?? null;
                        },

                        selectSubscriptionPlan(planType) {
                            this.subscriptionModal.planType = planType;
                            this.subscriptionModal.supportsProration = planType === 'monthly';
                            this.updateSubscriptionPreview();
                        },

                        extraDayEnabled() {
                            return Boolean(this.extraDay?.enabled);
                        },

                        extraDayCandidateIds() {
                            return (this.extraDay?.candidateCourseIds ?? []).map(id => Number(id));
                        },

                        extraDayCourseById(id) {
                            if (!id) return null;
                            const numeric = Number(id);
                            return this.courses.find(course => Number(course.id) === numeric) ?? null;
                        },

                        extraDayCandidates() {
                            if (!this.extraDayEnabled()) {
                                return [];
                            }

                            const candidateIds = this.extraDayCandidateIds();
                            if (!candidateIds.length) {
                                return [];
                            }

                            const baseCourseId = this.subscriptionModal.course?.id ?? null;

                            return this.courses
                                .filter(course => candidateIds.includes(Number(course.id)))
                                .filter(course => Number(course.id) !== Number(baseCourseId))
                                .filter(course => !this.isSubscribed(course.id));
                        },

                        handleExtraCourseChange(value) {
                            const parsed = value ? Number(value) : null;
                            this.subscriptionModal.selectedExtraCourseId = parsed || null;
                            this.updateSubscriptionPreview();
                        },

                        calculateSubscriptionAmount(option, startDate, plan) {
                            if (!plan) {
                                return { base: 0, extra: 0, total: 0 };
                            }

                            const extraCourse = this.subscriptionModal.selectedExtraCourseId
                                ? this.extraDayCourseById(this.subscriptionModal.selectedExtraCourseId)
                                : null;

                            const baseAmount = this.calculateBaseAmount(option, startDate, plan, this.subscriptionModal.course);
                            const extraAmount = extraCourse
                                ? this.calculateExtraDayAmount(extraCourse, plan, option, startDate)
                                : 0;

                            return {
                                base: Number(baseAmount.toFixed(2)),
                                extra: Number(extraAmount.toFixed(2)),
                                total: Number((baseAmount + extraAmount).toFixed(2)),
                            };
                        },

                        calculateBaseAmount(option, startDate, plan, course) {
                            if (!plan) return 0;
                            const basePrice = Number(plan?.amount ?? plan?.price ?? 0);
                            if (option === 'next_month' || plan.type !== 'monthly') {
                                return basePrice;
                            }

                            const target = startDate ? new Date(`${startDate}T00:00:00`) : new Date(`${this.isoToday()}T00:00:00`);
                            const periodStart = new Date(target.getFullYear(), target.getMonth(), 1);
                            const periodEnd = new Date(target.getFullYear(), target.getMonth() + 1, 0);

                            const courseStart = this.parseDate(course?.start_date ?? course?.startDate);
                            const courseEnd = this.parseDate(course?.end_date ?? course?.endDate);

                            const scheduleDays = this.normalizeScheduleDays(course?.schedule);
                            const lessonInfo = this.calculateLessonProration(scheduleDays, periodStart, periodEnd, target, courseStart, courseEnd);

                            if (lessonInfo.total > 0 && lessonInfo.remaining > 0) {
                                let lessonAmount = Number((basePrice * (lessonInfo.remaining / lessonInfo.total)).toFixed(2));
                                if (basePrice > 0 && lessonAmount < 0.01) {
                                    lessonAmount = 0.01;
                                }
                                return lessonAmount;
                            }

                            const totalDays = periodEnd.getDate();
                            const remainingDays = Math.max(totalDays - target.getDate() + 1, 0);
                            const ratio = totalDays > 0 ? remainingDays / totalDays : 1;
                            let amount = Number((basePrice * ratio).toFixed(2));

                            if (basePrice > 0 && amount < 0.01) {
                                amount = 0.01;
                            }

                            return amount;
                        },

                        calculateExtraDayAmount(extraCourse, plan, option, startDate) {
                            if (!extraCourse) return 0;
                            const monthlyPrice = Number(extraCourse?.monthly_price ?? extraCourse?.monthlyPrice ?? extraCourse?.price ?? 0);
                            const scheduleDays = this.normalizeScheduleDays(extraCourse?.schedule);
                            if (monthlyPrice <= 0 || !scheduleDays.length) {
                                return 0;
                            }

                            const lessonsPerWeek = Math.max(1, scheduleDays.length);
                            const planMonths = this.planMonths(plan?.type);
                            let basePlanAmount = Number(((monthlyPrice / lessonsPerWeek) * planMonths).toFixed(2));

                            const discountPercentRaw = extraCourse?.extra_day_discount_percent ?? extraCourse?.extraDayDiscountPercent ?? 0;
                            const discountPercent = Math.min(100, Math.max(0, Number(discountPercentRaw ?? 0)));
                            const discountAmount = Number((basePlanAmount * (discountPercent / 100)).toFixed(2));
                            let planAmount = Math.max(basePlanAmount - discountAmount, 0);
                            if (planAmount > 0 && planAmount < 0.01) {
                                planAmount = 0.01;
                            }

                            if (!(plan?.type === 'monthly' && option === 'current_month')) {
                                return planAmount;
                            }

                            const target = startDate ? new Date(`${startDate}T00:00:00`) : new Date(`${this.isoToday()}T00:00:00`);
                            const periodStart = new Date(target.getFullYear(), target.getMonth(), 1);
                            const periodEnd = new Date(target.getFullYear(), target.getMonth() + 1, 0);
                            const courseStart = this.parseDate(extraCourse?.start_date ?? extraCourse?.startDate);
                            const courseEnd = this.parseDate(extraCourse?.end_date ?? extraCourse?.endDate);

                            const lessonInfo = this.calculateLessonProration(scheduleDays, periodStart, periodEnd, target, courseStart, courseEnd);
                            if (lessonInfo.total > 0 && lessonInfo.remaining >= 0) {
                                let prorated = Number((planAmount * (lessonInfo.remaining / lessonInfo.total)).toFixed(2));
                                if (planAmount > 0 && prorated < 0.01) {
                                    prorated = 0.01;
                                }
                                return prorated;
                            }

                            return planAmount;
                        },

                        planMonths(planType) {
                            const map = { monthly: 1, quarterly: 3, annual: 12 };
                            return map[planType] ?? 1;
                        },

                        normalizeScheduleDays(rawSchedule) {
                            const items = normalize(rawSchedule ?? []);
                            const mapped = items
                                .map(slot => this.weekdayIndex(slot.day ?? slot.day_of_week ?? slot.label))
                                .filter((day, index, arr) => day !== null && arr.indexOf(day) === index);
                            return mapped;
                        },

                        weekdayIndex(label) {
                            if (!label) return null;
                            const normalized = label.toString().trim().toLowerCase();
                            const map = {
                                'domenica': 0, 'dom': 0, 'sunday': 0,
                                'lunedì': 1, 'lunedi': 1, 'lun': 1, 'monday': 1,
                                'martedì': 2, 'martedi': 2, 'mar': 2, 'tuesday': 2,
                                'mercoledì': 3, 'mercoledi': 3, 'mer': 3, 'wednesday': 3,
                                'giovedì': 4, 'giovedi': 4, 'gio': 4, 'thursday': 4,
                                'venerdì': 5, 'venerdi': 5, 'ven': 5, 'friday': 5,
                                'sabato': 6, 'sab': 6, 'saturday': 6,
                            };
                            return map[normalized] ?? null;
                        },

                        calculateLessonProration(scheduleDays, periodStart, periodEnd, clientStart, courseStart, courseEnd) {
                            if (!scheduleDays.length) {
                                return { total: 0, remaining: 0 };
                            }

                            const effectiveStart = courseStart && courseStart > periodStart ? courseStart : periodStart;
                            const effectiveEnd = courseEnd && courseEnd < periodEnd ? courseEnd : periodEnd;
                            if (effectiveStart > effectiveEnd) {
                                return { total: 0, remaining: 0 };
                            }

                            const countWeekday = (startDate, endDate, weekday) => {
                                const first = new Date(startDate.getTime());
                                const diff = (weekday - first.getDay() + 7) % 7;
                                first.setDate(first.getDate() + diff);

                                if (first > endDate) return 0;

                                const last = new Date(endDate.getTime());
                                const backDiff = (last.getDay() - weekday + 7) % 7;
                                last.setDate(last.getDate() - backDiff);

                                if (first > last) return 0;

                                return Math.floor((last - first) / (7 * 24 * 60 * 60 * 1000)) + 1;
                            };

                            let total = 0;
                            let remaining = 0;
                            scheduleDays.forEach(weekday => {
                                total += countWeekday(effectiveStart, effectiveEnd, weekday);
                                const remainingStart = clientStart > effectiveStart ? clientStart : effectiveStart;
                                if (remainingStart <= effectiveEnd) {
                                    remaining += countWeekday(remainingStart, effectiveEnd, weekday);
                                }
                            });

                            return { total, remaining };
                        },

                        updateSubscriptionPreview() {
                            if (!this.subscriptionModal.course) {
                                this.subscriptionModal.preview = '0.00';
                                return;
                            }

                            const plan = this.selectedPlan();
                            if (!plan) {
                                this.subscriptionModal.preview = '0.00';
                                return;
                            }

                            const amounts = this.calculateSubscriptionAmount(
                                this.subscriptionModal.option,
                                this.subscriptionModal.startDate,
                                plan
                            );
                            this.subscriptionModal.previewBase = amounts.base.toFixed(2);
                            this.subscriptionModal.previewExtra = amounts.extra.toFixed(2);
                            this.subscriptionModal.preview = amounts.total.toFixed(2);
                        },

                        openSubscriptionModal(course) {
                            if (!course || this.isSubscribed(course.id)) {
                                this.statusMessage = this.isSubscribed(course?.id) ? 'Sei già iscritto a questo corso.' : this.statusMessage;
                                return;
                            }

                            const plans = this.coursePlans(course);
                            if (!plans.length) {
                                this.statusMessage = 'Non ci sono piani di abbonamento disponibili per questo corso.';
                                return;
                            }

                            const today = this.isoToday();
                            const endOfMonth = this.isoEndOfMonth();
                            const nextMonthStart = this.isoNextMonthStart();

                            this.subscriptionModal.course = {
                                ...course,
                                availablePlans: plans,
                            };
                            this.subscriptionModal.planType = plans[0]?.type ?? null;
                            this.subscriptionModal.supportsProration = this.subscriptionModal.planType === 'monthly';
                            this.subscriptionModal.option = 'current_month';
                            this.subscriptionModal.startDate = today;
                            this.subscriptionModal.selectedExtraCourseId = null;
                            this.subscriptionModal.previewBase = '0.00';
                            this.subscriptionModal.previewExtra = '0.00';
                            this.subscriptionModal.limits = {
                                today,
                                endOfMonth,
                                nextMonthStart,
                            };
                            this.subscriptionModal.nextMonthLabel = this.formatDateLabel(nextMonthStart);
                            this.updateSubscriptionPreview();
                            this.subscriptionModal.open = true;
                        },

                        closeSubscriptionModal() {
                            this.subscriptionModal.open = false;
                            this.subscriptionModal.course = null;
                            this.subscriptionModal.planType = null;
                            this.subscriptionModal.supportsProration = true;
                            this.subscriptionModal.startDate = null;
                            this.subscriptionModal.preview = '0.00';
                            this.subscriptionModal.previewBase = '0.00';
                            this.subscriptionModal.previewExtra = '0.00';
                            this.subscriptionModal.selectedExtraCourseId = null;
                        },

                        handleSubscriptionOptionChange(option) {
                            this.subscriptionModal.option = option;
                            if (option === 'current_month') {
                                this.subscriptionModal.startDate = this.subscriptionModal.limits.today ?? this.isoToday();
                            } else {
                                this.subscriptionModal.startDate = this.subscriptionModal.limits.nextMonthStart ?? this.isoNextMonthStart();
                            }
                            this.updateSubscriptionPreview();
                        },

                        handleSubscriptionDateChange(value) {
                            this.subscriptionModal.startDate = value;
                            this.updateSubscriptionPreview();
                        },

                        isValidSubscriptionDate() {
                            if (this.subscriptionModal.option !== 'current_month') {
                                return true;
                            }
                            const date = this.subscriptionModal.startDate;
                            if (!date) return false;
                            return (
                                (!this.subscriptionModal.limits.today || date >= this.subscriptionModal.limits.today) &&
                                (!this.subscriptionModal.limits.endOfMonth || date <= this.subscriptionModal.limits.endOfMonth)
                            );
                        },

                        canSubmitSubscription() {
                            if (!this.subscriptionModal.course) return false;
                            if (!this.subscriptionModal.planType || !this.selectedPlan()) return false;
                            if (this.isSubscribed(this.subscriptionModal.course.id)) return false;
                            if (this.subscriptionModal.option === 'current_month') {
                                return this.isValidSubscriptionDate();
                            }
                            return true;
                        },

                        confirmSubscription() {
                            if (!this.subscriptionModal.course || !this.canSubmitSubscription()) {
                                return;
                            }

                            const plan = this.selectedPlan();
                            if (!plan) {
                                this.errorMessage = 'Seleziona un piano di abbonamento valido.';
                                return;
                            }

                            const courseTitle = this.subscriptionModal.course.title ?? 'Questo corso';
                            const option = this.subscriptionModal.option === 'current_month'
                                ? 'a partire da questo mese'
                                : 'a partire dal prossimo mese';
                            const amount = this.subscriptionModal.preview;
                            const planLabel = plan.label ?? plan.type;

                            if (!window.confirm(`Confermi l'iscrizione a \"${courseTitle}\" (${planLabel}) ${option} con un addebito di € ${amount}?`)) {
                                return;
                            }

                            const payload = {
                                course_id: this.subscriptionModal.course.id,
                                start_option: this.subscriptionModal.option,
                                plan_type: plan.type,
                            };

                            if (this.subscriptionModal.option === 'current_month') {
                                payload.start_date = this.subscriptionModal.startDate;
                            }
                            if (this.subscriptionModal.selectedExtraCourseId) {
                                payload.extra_course_id = this.subscriptionModal.selectedExtraCourseId;
                            }

                            this.sendRequest(this.routes.subscribe, 'POST', payload)
                                .then(response => {
                                    if (response.subscription) {
                                        const existing = this.subscriptions.find(sub => sub.id === response.subscription.id);
                                        if (existing) {
                                            Object.assign(existing, response.subscription);
                                        } else {
                                            this.subscriptions.push(response.subscription);
                                        }
                                        this.refreshDerivedCollections();
                                    }
                                    this.statusMessage = response.message || 'Subscription activated.';
                                    if (response.payment) {
                                        this.upsertPayment(response.payment);
                                    }
                                    this.closeSubscriptionModal();
                                })
                                .catch(() => {});
                        },

                        membershipStatusClass() {
                            const status = this.membership?.status;
                            if (status === 'active') return 'bg-emerald-100 text-emerald-700';
                            if (status === 'pending') return 'bg-amber-100 text-amber-700';
                            return 'bg-stone-200 text-stone-600';
                        },

                        membershipStatusLabel() {
                            const status = this.membership?.status ?? 'pending';
                            const labels = {
                                active: 'Attiva',
                                pending: 'In attesa di pagamento',
                                expired: 'Scaduta',
                            };
                            return labels[status] ?? status;
                        },

                        membershipDueLabel() {
                            if (!this.membership) return '—';
                            if (this.membership.status === 'active' && this.membership.paid_at) {
                                return `Pagata il ${this.formatDateString(this.membership.paid_at)}`;
                            }
                            return this.membership.due_date
                                ? `Scadenza pagamento: ${this.formatDateString(this.membership.due_date)}`
                                : 'Scadenza non impostata';
                        },

                        openBookingModal(teacherId) {
                            const teacher = this.teachers.find(t => t.id === teacherId);
                            if (!teacher) return;
                            this.bookingModal = {
                                open: true,
                                teacherId,
                                teacher,
                                selectedSlot: null,
                            };
                            this.errorMessage = '';
                        },

                        closeBookingModal() {
                            this.bookingModal.open = false;
                            this.bookingModal.selectedSlot = null;
                        },

                        availableSlots(teacher) {
                            if (!teacher) return [];
                            const now = new Date();
                            return normalize(teacher.availability || [])
                                .filter(slot => !slot.is_booked && slot.date && slot.time)
                                .map(slot => {
                                    const dateTime = this.toDate(slot.date, slot.time);
                                    return {
                                        ...slot,
                                        dateTime,
                                        displayDate: dateTime.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }),
                                        displayTime: dateTime.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }),
                                        untilLabel: this.untilLabel(dateTime),
                                        inFuture: dateTime > now,
                                    };
                                })
                                .filter(slot => slot.inFuture)
                                .sort((a, b) => a.dateTime - b.dateTime);
                        },

                        confirmBooking() {
                            if (!this.bookingModal.teacher || !this.bookingModal.selectedSlot) return;
                            const slot = this.bookingModal.selectedSlot;
                            const teacherName = this.bookingModal.teacher?.name ?? 'insegnante';
                            const slotDate = this.formatDateString(slot.date) || slot.date;
                            const slotTime = slot.displayTime || slot.time;

                            if (!window.confirm(`Confermi la prenotazione con ${teacherName} il ${slotDate} alle ${slotTime}?`)) {
                                return;
                            }

                            const payload = {
                                teacher_id: this.bookingModal.teacherId,
                                availability_id: slot.id,
                            };

                            this.sendRequest(this.routes.book, 'POST', payload)
                                .then((data) => {
                                    if (data.booking) {
                                        this.updateAfterBooking(data.booking);
                                        this.statusMessage = data.message || 'Lesson booked successfully.';
                                    }
                                    if (data.slot) {
                                        this.markSlotBooked(data.slot.teacher_id, data.slot.id);
                                    }
                                    if (data.payment) {
                                        this.upsertPayment(data.payment);
                                    }
                                    this.closeBookingModal();
                                })
                                .catch(() => {});
                        },

                        cancelBooking(bookingId) {
                            const booking = this.bookings.find(b => b.id === bookingId);
                            if (!booking || !this.canCancel(booking)) {
                                return;
                            }
                            const url = `${this.routes.cancelBase}/${bookingId}`;
                            this.sendRequest(url, 'DELETE')
                                .then((data) => {
                                this.bookings = this.bookings.filter(b => b.id !== bookingId);
                                if (data.slot) {
                                    this.markSlotAvailable(data.slot.teacher_id, data.slot.id);
                                }
                                this.statusMessage = data.message || 'Booking cancelled successfully.';
                                if (data.payment_id) {
                                    this.removePayment(data.payment_id);
                                }
                            })
                            .catch(() => {});
                        },

                        subscribeToCourse(courseId) {
                            const course = this.courses.find(c => c.id === courseId);
                            if (!course) return;
                            this.openSubscriptionModal(course);
                        },

                        cancelSubscription(courseId) {
                            const subscription = this.subscriptionByCourse(courseId);
                            if (!subscription) {
                                this.statusMessage = 'Nessuna iscrizione attiva da annullare.';
                                return;
                            }

                            this.cancelSubscriptionInternal(subscription);
                        },

                        cancelSubscriptionById(subscriptionId) {
                            const subscription = this.findSubscriptionById(subscriptionId);
                            if (!subscription) {
                                this.statusMessage = 'Iscrizione non trovata.';
                                return;
                            }

                            this.cancelSubscriptionInternal(subscription);
                        },

                        cancelSubscriptionInternal(subscription) {
                            if (!subscription || subscription.status === 'cancelled') {
                                this.statusMessage = 'Questa iscrizione è già stata annullata.';
                                return;
                            }

                            if (!window.confirm('Confermi la cancellazione della tua partecipazione a questo corso?')) {
                                return;
                            }

                            const url = `${this.routes.cancelSubscription}/${subscription.id}`;
                            this.sendRequest(url, 'DELETE')
                                .then(response => {
                                    if (response.subscription) {
                                        const target = this.findSubscriptionById(response.subscription.id);
                                        if (target) {
                                            Object.assign(target, response.subscription);
                                        } else {
                                            this.subscriptions.push(response.subscription);
                                        }
                                    } else {
                                        this.subscriptions = this.subscriptions.filter(sub => sub.id !== subscription.id);
                                    }

                                    const removedIds = Array.isArray(response.removed_payment_ids)
                                        ? response.removed_payment_ids
                                        : (response.payment_id ? [response.payment_id] : []);

                                    removedIds.forEach(id => this.removePayment(id));

                                    this.statusMessage = response.message || 'Iscrizione annullata.';
                                    this.refreshDerivedCollections();
                                })
                                .catch(() => {});
                        },

                        updateAfterBooking(booking) {
                            const decorated = decorateBooking(booking) ?? booking;
                            if ((!decorated.teacher || (!decorated.teacher.email && !decorated.teacher.whatsappUrl)) && decorated.teacherId) {
                                const fallbackTeacher = this.teachers.find(t => t.id === decorated.teacherId);
                                if (fallbackTeacher) {
                                    decorated.teacher = {
                                        ...decorateTeacher(fallbackTeacher),
                                    };
                                }
                            }
                            const exists = this.bookings.find(b => b.id === decorated.id);
                            if (exists) {
                                Object.assign(exists, decorated);
                            } else {
                                this.bookings.push({
                                    ...decorated,
                                    teacher: decorated.teacher || { id: decorated.teacherId },
                                    date: decorated.slot?.date ?? decorated.date,
                                    time: decorated.slot?.time ?? decorated.time,
                                });
                            }
                            this.refreshDerivedCollections();
                        },

                        markSlotBooked(teacherId, slotId) {
                            const teacher = this.teachers.find(t => t.id === teacherId);
                            if (!teacher) return;
                            teacher.availability = normalize(teacher.availability).map(slot => {
                                if (slot.id === slotId) {
                                    return { ...slot, is_booked: true, booked_by_id: this.clientId };
                                }
                                return slot;
                            });
                        },

                        markSlotAvailable(teacherId, slotId) {
                            const teacher = this.teachers.find(t => t.id === teacherId);
                            if (!teacher) return;
                            teacher.availability = normalize(teacher.availability).map(slot => {
                                if (slot.id === slotId) {
                                    return { ...slot, is_booked: false, booked_by_id: null };
                                }
                                return slot;
                            });
                        },

                    sendRequest(url, method = 'POST', payload = null) {
                        this.loading = true;
                        this.errorMessage = '';

                        const options = {
                            method,
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                ...(this.csrfToken ? { 'X-CSRF-TOKEN': this.csrfToken } : {}),
                            },
                            credentials: 'same-origin',
                        };

                        if (payload && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                            options.body = JSON.stringify(payload);
                        }

                        return fetch(url, options).then(async response => {
                            let data = {};
                            try {
                                data = await response.json();
                            } catch (_) {
                                data = {};
                                }
                                if (!response.ok) {
                                    const errors = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'An error occurred.');
                                    this.errorMessage = errors;
                                    throw new Error(errors);
                                }
                                return data;
                            }).catch(error => {
                                if (!this.errorMessage) {
                                    this.errorMessage = error.message || 'An unexpected error occurred.';
                                }
                                throw error;
                        }).finally(() => {
                            this.loading = false;
                        });
                    },

                    formatMoney(value) {
                        const amount = Number(value ?? 0);
                        return amount.toLocaleString(undefined, { style: 'currency', currency: 'EUR' });
                    },

                    formatDateString(value) {
                        if (!value) return '';
                        const normalized = typeof value === 'string' ? value.replace(' ', 'T') : value;
                        const date = new Date(normalized);
                        if (Number.isNaN(date.getTime())) return value;
                        return date.toLocaleDateString();
                    },

                    paymentStatusClass(status) {
                        if (status === 'paid') return 'bg-emerald-100 text-emerald-700';
                        return 'bg-amber-100 text-amber-700';
                    },

                    upsertPayment(payment) {
                        const existing = this.payments.find(p => p.id === payment.id);
                        if (existing) {
                            Object.assign(existing, payment);
                        } else {
                            this.payments.unshift(payment);
                        }
                        this.payments = this.payments.sort((a, b) => new Date(b.paid_at || b.due_date || 0) - new Date(a.paid_at || a.due_date || 0));
                    },

                    removePayment(paymentId) {
                        this.payments = this.payments.filter(p => p.id !== paymentId);
                    },

                        findSubscriptionByCourse(courseId) {
                            return this.subscriptions.find(sub => (sub.course_id ?? sub.courseId) === courseId) || null;
                        },

                        findSubscriptionById(subscriptionId) {
                            return this.subscriptions.find(sub => sub.id === subscriptionId) || null;
                        },

                        subscriptionByCourse(courseId) {
                            const subscription = this.findSubscriptionByCourse(courseId);
                            if (!subscription) return null;
                            return subscription.status === 'cancelled' ? null : subscription;
                        },

                        isSubscribed(courseId) {
                            return Boolean(this.subscriptionByCourse(courseId));
                        },

                        canCancelSubscription(courseId) {
                            const subscription = this.subscriptionByCourse(courseId);
                            if (!subscription) return false;

                            const hasPaidPayment = this.payments.some(payment =>
                                payment.type === 'course_subscription'
                                && (payment.course_id ?? null) === courseId
                                && payment.status === 'paid'
                            );

                            return !hasPaidPayment;
                        },

                        activeSubscriptions() {
                            return this.subscriptions.filter(sub => sub.status !== 'cancelled');
                        },

                        displaySubscriptions() {
                            return this.subscriptions
                                .slice()
                                .sort((a, b) => {
                                    if (a.status === b.status) {
                                        const titleA = (a.course?.title ?? '').toLocaleLowerCase();
                                        const titleB = (b.course?.title ?? '').toLocaleLowerCase();
                                        return titleA.localeCompare(titleB);
                                    }
                                    return a.status === 'active' ? -1 : 1;
                                });
                        },

                        upcomingSorted() {
                            return this.bookings.slice().sort((a, b) => this.dateValue(a) - this.dateValue(b));
                        },

                        get upcomingBookings() {
                            return this.upcomingSorted();
                        },

                        canCancel(booking) {
                            const minutes = this.minutesUntil(booking);
                            return minutes !== null && minutes >= 24 * 60;
                        },

                        cancelCountdownLabel(booking) {
                            const minutes = this.minutesUntil(booking);
                            if (minutes === null) {
                                return 'Awaiting schedule confirmation.';
                            }
                            if (minutes < 0) {
                                return 'This lesson can no longer be cancelled online.';
                            }
                            if (minutes < 24 * 60) {
                                return 'Less than 24 hours remain; contact the studio to cancel.';
                            }
                            const hours = Math.floor(minutes / 60);
                            const mins = minutes % 60;
                            return `You can cancel up to ${hours}h ${mins}m before the lesson.`;
                        },

                        formatDate(booking) {
                            const date = this.toDateFromBooking(booking);
                            return date ? date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }) : 'Date to be confirmed';
                        },

                        formatTime(booking) {
                            const date = this.toDateFromBooking(booking);
                            return date ? date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }) : 'Time to be confirmed';
                        },

                        dateValue(booking) {
                            const date = this.toDateFromBooking(booking);
                            return date ? date.getTime() : Number.MAX_SAFE_INTEGER;
                        },

                        toDate(date, time) {
                            if (!date || !time) return null;
                            return new Date(`${date}T${time}:00`);
                        },

                        toDateFromBooking(booking) {
                            const slot = booking.slot || {};
                            const date = booking.date ?? slot.date;
                            const time = booking.time ?? slot.time;
                            return this.toDate(date, time);
                        },

                        minutesUntil(booking) {
                            const date = this.toDateFromBooking(booking);
                            if (!date) return null;
                            const diffMs = date.getTime() - Date.now();
                            return Math.round(diffMs / 60000);
                        },

                        untilLabel(date) {
                            const diffMs = date.getTime() - Date.now();
                            const diffMinutes = Math.round(diffMs / 60000);
                            if (diffMinutes <= 0) return 'Starts soon';
                            const hours = Math.floor(diffMinutes / 60);
                            const mins = diffMinutes % 60;
                            if (hours >= 24) {
                                const days = Math.floor(hours / 24);
                                return `${days} day${days === 1 ? '' : 's'} left`;
                            }
                            if (hours > 0) {
                                return `${hours}h ${mins}m left`;
                            }
                            return `${mins} minutes left`;
                        },
                    };
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/partials/client.blade.php ENDPATH**/ ?>