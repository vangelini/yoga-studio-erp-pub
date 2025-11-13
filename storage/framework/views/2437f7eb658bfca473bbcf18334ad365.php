<?php
    $activeTeacher = $teachers->firstWhere('id', auth()->id());
    $teacherReceipts = collect($receipts ?? []);
    $teacherProfile = $teacher_profile ?? null;
    $teacherCourses = collect($teacher_courses ?? []);
    $teacherCoursePayments = collect($teacher_course_payments ?? []);
    $teacherMembershipPayments = collect($teacher_membership_payments ?? []);
    $teacherStudents = collect($teacher_students ?? []);
    $dayOptions = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
    $teacherCourseCount = $teacherCourses->count();
    $teacherStudentCount = $teacherStudents->count();
    $teacherPendingPaymentsCount = $teacherCoursePayments->where('status', 'pending')->count() + $teacherMembershipPayments->where('status', 'pending')->count();
?>

<section class="space-y-10">
    <div class="relative overflow-hidden rounded-2xl border border-teal-200/40 bg-gradient-to-r from-teal-600 via-teal-500 to-emerald-500 text-white shadow-lg">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/honeycomb.png')] opacity-20 pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Dashboard docente</p>
                <h2 class="text-3xl md:text-4xl font-semibold">Gestisci corsi, pagamenti e allievi autorizzati</h2>
                <p class="text-white/85 leading-relaxed">
                    Tutti i moduli sono identici a quelli della console amministrativa. Le azioni disponibili rispettano i permessi assegnati dal centro.
                </p>
            </div>
            <div class="grid grid-cols-3 gap-4 bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/30 shadow-inner text-center text-xs uppercase tracking-widest">
                <div class="flex flex-col text-white/80">
                    <span>Corsi</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherCourseCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Allievi</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherStudentCount); ?></span>
                </div>
                <div class="flex flex-col text-white/80">
                    <span>Pendenze</span>
                    <span class="text-2xl font-semibold text-white"><?php echo e($teacherPendingPaymentsCount); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if(($teacherProfile['can_manage_courses'] ?? false)): ?>
        <?php echo $__env->make('dashboard.partials.admin-courses', [
            'courses' => $teacherCourses,
            'teacherOptions' => collect([auth()->id() => auth()->user()->name]),
            'dayOptions' => $dayOptions,
            'allowCourseCreation' => false,
            'allowTeacherSelection' => false,
            'courseCardTitle' => 'I miei corsi',
            'courseCardSubtitle' => 'Gestisci e aggiorna i corsi che ti sono stati assegnati.',
            'currentTeacherId' => auth()->id(),
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php if(($teacherProfile['can_manage_payments'] ?? false)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-6 space-y-4 bg-white border border-stone-200 rounded-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-stone-900">Pagamenti corsi</h3>
                        <p class="text-sm text-stone-500">Pendenze relative ai corsi che segui.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-stone-200">
                        <thead class="bg-stone-100 text-stone-600 uppercase text-[11px]">
                            <tr>
                                <th class="px-3 py-2 text-left">Allievo</th>
                                <th class="px-3 py-2 text-left">Corso</th>
                                <th class="px-3 py-2 text-left">Scadenza</th>
                                <th class="px-3 py-2 text-left">Importo</th>
                                <th class="px-3 py-2 text-left">Stato</th>
                                <th class="px-3 py-2 text-left">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <?php $__empty_1 = true; $__currentLoopData = $teacherCoursePayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-stone-50">
                                    <td class="px-3 py-2">
                                        <p class="font-semibold text-stone-800"><?php echo e($payment['user_name'] ?? '—'); ?></p>
                                        <p class="text-xs text-stone-500">ID #<?php echo e($payment['id']); ?></p>
                                    </td>
                                    <td class="px-3 py-2 text-stone-600"><?php echo e($payment['course_title'] ?? 'Corso'); ?></td>
                                    <td class="px-3 py-2 text-stone-600"><?php echo e($payment['due_date_display'] ?? '—'); ?></td>
                                    <td class="px-3 py-2 font-semibold text-teal-700">€ <?php echo e($payment['amount_formatted']); ?></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($payment['status_badge']); ?>"><?php echo e(ucfirst($payment['status'])); ?></span>
                                    </td>
                                    <td class="px-3 py-2 space-y-2">
                                        <?php if($payment['status'] === 'pending'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.payments.update', $payment['id'])); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="cash">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-teal-700 transition">Segna pagato</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.payments.update', $payment['id'])); ?>" onsubmit="return confirm('Annullare il mese per <?php echo e($payment['user_name']); ?>?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="waive">
                                                <input type="text" name="reason" class="input-field text-xs" placeholder="Motivo annullamento" required>
                                                <button type="submit" class="mt-1 inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1 text-[11px] font-semibold text-stone-600 hover:bg-stone-100 transition">Annulla mese</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-[11px] text-stone-400">Nessuna azione</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-sm text-stone-500">Non ci sono pendenze aperte per i tuoi corsi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-6 space-y-4 bg-white border border-stone-200 rounded-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-stone-900">Quote associative</h3>
                        <p class="text-sm text-stone-500">Solo gli allievi iscritti ai tuoi corsi.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-stone-200">
                        <thead class="bg-stone-100 text-stone-600 uppercase text-[11px]">
                            <tr>
                                <th class="px-3 py-2 text-left">Allievo</th>
                                <th class="px-3 py-2 text-left">Scadenza</th>
                                <th class="px-3 py-2 text-left">Importo</th>
                                <th class="px-3 py-2 text-left">Stato</th>
                                <th class="px-3 py-2 text-left">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <?php $__empty_1 = true; $__currentLoopData = $teacherMembershipPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-stone-50">
                                    <td class="px-3 py-2">
                                        <p class="font-semibold text-stone-800"><?php echo e($payment['user_name'] ?? '—'); ?></p>
                                        <p class="text-xs text-stone-500">ID #<?php echo e($payment['id']); ?></p>
                                    </td>
                                    <td class="px-3 py-2 text-stone-600"><?php echo e($payment['due_date_display'] ?? '—'); ?></td>
                                    <td class="px-3 py-2 font-semibold text-teal-700">€ <?php echo e($payment['amount_formatted']); ?></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($payment['status_badge']); ?>"><?php echo e(ucfirst($payment['status'])); ?></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php if($payment['status'] === 'pending'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.payments.update', $payment['id'])); ?>" onsubmit="return confirm('Confermi il pagamento della quota associativa per <?php echo e($payment['user_name']); ?>?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="cash">
                                                <input type="hidden" name="reason" value="">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-teal-700 transition">Registra pagamento</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-[11px] text-stone-400">Nessuna azione</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-sm text-stone-500">Nessuna quota associativa pendente per i tuoi allievi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 lg:col-span-2">
                <h3 class="text-2xl font-semibold text-stone-800 mb-4">Prossime lezioni</h3>
                <div class="space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 bg-stone-50 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-lg font-semibold text-teal-700">
                                    <?php echo e($booking['date'] ? \Carbon\Carbon::parse($booking['date'])->translatedFormat('l d F') : 'Data da confermare'); ?>

                                </p>
                                <p class="text-stone-600 text-sm">
                                    <?php echo e($booking['time'] ?? 'Orario da confermare'); ?> · con <?php echo e(optional($booking['client'])->name ?? 'Cliente'); ?>

                                </p>
                            </div>
                            <?php ($contactEmail = optional($booking['client'])->email); ?>
                            <a
                                href="<?php echo e($contactEmail ? 'mailto:' . $contactEmail : '#'); ?>"
                                class="text-teal-600 text-sm font-semibold hover:text-teal-800 underline <?php echo e($contactEmail ? '' : 'pointer-events-none opacity-50'); ?>"
                            >
                                Contatta
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-stone-500 text-sm">Non hai lezioni private in programma.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($teacherProfile['can_manage_students'] ?? false)): ?>
        <div class="card p-6 space-y-4 bg-white border border-stone-200 rounded-2xl">
            <div class="flex flex-col gap-1">
                <h3 class="text-2xl font-semibold text-stone-900">Allievi assegnati</h3>
                <p class="text-sm text-stone-500">Panoramica degli iscritti ai tuoi corsi.</p>
            </div>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $teacherStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-stone-200 rounded-xl px-4 py-3 bg-stone-50 space-y-3">
                        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-lg font-semibold text-stone-800"><?php echo e($student['name']); ?></p>
                                <p class="text-xs text-stone-500">Stato account: <?php echo e($student['status']); ?></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($student['membership_pending'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                    <?php echo e($student['membership_pending'] ? 'Quota associativa da saldare' : 'Quota in regola'); ?>

                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($student['missing_documents'] ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                    <?php echo e($student['missing_documents'] ? $student['missing_documents'] . ' documenti mancanti' : 'Documenti completi'); ?>

                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-stone-600">
                            <?php if($student['email']): ?>
                                <a href="mailto:<?php echo e($student['email']); ?>" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800"><?php echo e($student['email']); ?></a>
                            <?php endif; ?>
                            <?php if($student['telephone']): ?>
                                <?php if($student['whatsapp']): ?>
                                    <a href="<?php echo e($student['whatsapp']); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-emerald-600 font-semibold hover:text-emerald-700"><?php echo e($student['telephone']); ?></a>
                                <?php else: ?>
                                    <span><?php echo e($student['telephone']); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-stone-500 font-semibold">Corsi frequentati</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php $__currentLoopData = $student['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold <?php echo e($course['badge']); ?>">
                                        <?php echo e($course['course_title']); ?> · <?php echo e($course['plan']); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-stone-500">Non risultano allievi iscritti ai tuoi corsi.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($private_lessons_enabled ?? false): ?>
    <div
        x-data="Object.assign(teacherAvailability({
            initialAvailability: <?php echo json_encode($activeTeacher['availability'] ?? [], 15, 512) ?>,
            fetchUrl: '<?php echo e(url('/api/teachers/' . auth()->id() . '/availability')); ?>'
        }), { calendarOpen: false })"
        x-init="init()"
        class="bg-white rounded-xl shadow-sm border border-stone-200 p-6"
    >
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-2xl font-semibold text-stone-800">Disponibilità lezioni personali</h3>
                <p class="text-sm text-stone-500">Clicca sugli slot orari per aggiornare rapidamente la tua agenda.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">

                <button
                    type="button"
                    class="inline-flex items-center gap-2 bg-white text-stone-700 border border-stone-200 px-4 py-2 rounded-lg hover:bg-stone-100 transition-colors"
                    @click="calendarOpen = !calendarOpen"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8h16M4 16h16" />
                    </svg>
                    <span x-text="calendarOpen ? 'Nascondi calendario' : 'Mostra calendario'"></span>
                </button>
            </div>
        </div>

        <div x-show="calendarOpen" x-transition.opacity x-cloak class="space-y-6">
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="bg-stone-200 text-stone-700 px-4 py-2 rounded-lg hover:bg-stone-300 transition-colors"
                    @click="prevMonth"
                >
                    Precedente
                </button>
                <div class="text-center">
                    <div class="text-xs uppercase tracking-wider text-stone-400">Mese</div>
                    <div class="text-lg font-semibold text-stone-700" x-text="currentMonthLabel"></div>
                </div>
                <button
                    type="button"
                    class="bg-stone-200 text-stone-700 px-4 py-2 rounded-lg hover:bg-stone-300 transition-colors"
                    @click="nextMonth"
                >
                    Successivo
                </button>
                </div>
            <div class="grid grid-cols-7 gap-2 text-center text-xs md:text-sm font-semibold text-stone-500 uppercase tracking-wide">
                <template x-for="day in weekdays" :key="day">
                    <div class="py-2" x-text="day"></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-2">
                <template x-for="day in days()" :key="day.key">
                    <div
                        class="border rounded-lg p-2 md:p-3 flex flex-col gap-2 bg-white transition duration-150"
                        :class="{
                            'opacity-50': !day.isCurrentMonth,
                            'ring-2 ring-teal-500': day.isToday
                        }"
                    >
                        <div class="flex justify-between items-center">
                            <span class="text-base md:text-lg font-semibold text-stone-700" x-text="day.display"></span>
                            <span class="text-xs text-stone-400" x-text="day.shortLabel"></span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <template x-for="time in timeSlots" :key="time">
                                <button
                                    type="button"
                                    class="w-full text-center rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm font-medium transition-all"
                                    :class="slotClasses(day.iso, time, day.isPast)"
                                    :disabled="day.isPast || isBooked(slotKey(day.iso, time))"
                                    @click="toggleSlot(day.iso, time)"
                                >
                                    <span x-text="time"></span>
                                    <template x-if="isBooked(slotKey(day.iso, time))">
                                        <span class="block text-[10px] md:text-xs font-normal mt-1">
                                            Prenotato da <span class="font-semibold" x-text="booked[slotKey(day.iso, time)].bookedByName ?? 'Cliente'"></span>
                                        </span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                <div class="text-sm space-y-1">
                    <p class="text-stone-500">Ricordati di re i cambi cliccando su “Salva”.</p>
                    <template x-if="statusMessage">
                        <p class="text-emerald-600 font-medium" x-text="statusMessage"></p>
                    </template>
                    <template x-if="errorMessage">
                        <p class="text-rose-600 font-medium" x-text="errorMessage"></p>
                    </template>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="bg-stone-200 text-stone-700 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="resetChanges"
                        :disabled="!isDirty() || saving"
                    >
                        Ripristina
                    </button>
                    <button
                        type="button"
                        class="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-400 disabled:cursor-not-allowed"
                        @click="saveChanges"
                        :disabled="!isDirty() || saving"
                    >
                        <span x-show="!saving">Salva</span>
                        <span x-show="saving">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</section>

<?php if (! $__env->hasRenderedOnce('4aaa28e1-fbd4-47a2-b3f1-5f5547c55957')): $__env->markAsRenderedOnce('4aaa28e1-fbd4-47a2-b3f1-5f5547c55957'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
            Alpine.data('teacherAvailability', ({ initialAvailability, fetchUrl }) => ({
                weekdays: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
                timeSlots: ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'],
                currentDate: new Date(),
                originalList: [],
                originalEditable: [],
                editable: new Set(),
                booked: {},
                statusMessage: '',
                errorMessage: '',
                saving: false,
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',

                init() {
                    this.loadAvailability(initialAvailability);
                },

                loadAvailability(list) {
                    this.originalList = JSON.parse(JSON.stringify(list || []));
                    this.booked = {};
                    this.editable = new Set();
                    const editableKeys = [];

                    (list || []).forEach(slot => {
                        const key = this.slotKey(slot.date, slot.time);
                        if (slot.is_booked) {
                            this.booked[key] = slot;
                        } else {
                            this.editable.add(key);
                            editableKeys.push(key);
                        }
                    });

                    this.originalEditable = [...editableKeys].sort();
                    this.statusMessage = '';
                    this.errorMessage = '';
                },

                prevMonth: function () {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                },

                nextMonth: function () {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                },

                days() {
                    const startOfMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
                    const startDay = startOfMonth.getDay();
                    const startDate = new Date(startOfMonth);
                    startDate.setDate(startDate.getDate() - startDay);

                    const days = [];
                    const today = this.todayString();

                    for (let i = 0; i < 42; i++) {
                        const date = new Date(startDate);
                        date.setDate(startDate.getDate() + i);

                        const iso = this.formatDate(date);
                        days.push({
                            key: iso,
                            iso,
                            display: date.getDate(),
                            shortLabel: this.weekdays[date.getDay()],
                            isCurrentMonth: date.getMonth() === this.currentDate.getMonth(),
                            isToday: iso === today,
                            isPast: date < new Date(new Date().setHours(0, 0, 0, 0))
                        });
                    }

                    return days;
                },

                slotKey(date, time) {
                    return `${date}T${time}`;
                },

                isBooked(key) {
                    return Boolean(this.booked[key]);
                },

                slotClasses(date, time, isPast) {
                    const key = this.slotKey(date, time);

                    if (this.isBooked(key)) {
                        return 'bg-red-600 text-white cursor-not-allowed';
                    }

                    if (isPast) {
                        return 'bg-stone-100 text-stone-400 cursor-not-allowed opacity-50';
                    }

                    if (this.editable.has(key)) {
                        if (this.originalEditable.includes(key)) {
                            return 'bg-blue-500 text-white hover:bg-blue-600';
                        }
                        return 'bg-green-500 text-white hover:bg-green-600';
                    }

                    if (this.originalEditable.includes(key)) {
                        return 'bg-rose-200 text-rose-800 hover:bg-rose-300';
                    }

                    return 'bg-stone-100 text-stone-500 hover:bg-stone-200';
                },

                toggleSlot(date, time) {
                    const key = this.slotKey(date, time);

                    if (this.isBooked(key)) {
                        return;
                    }

                    if (this.editable.has(key)) {
                        this.editable.delete(key);
                    } else {
                        this.editable.add(key);
                    }
                },

                isDirty() {
                    const current = Array.from(this.editable).sort();
                    if (current.length !== this.originalEditable.length) {
                        return true;
                    }

                    for (let i = 0; i < current.length; i++) {
                        if (current[i] !== this.originalEditable[i]) {
                            return true;
                        }
                    }

                    return false;
                },

                resetChanges() {
                    this.loadAvailability(this.originalList);
                },

                availabilityPayload() {
                    return Array.from(this.editable).map(key => {
                        const [date, time] = key.split('T');
                        return { date, time };
                    });
                },

                async saveChanges() {
                    this.saving = true;
                    this.statusMessage = '';
                    this.errorMessage = '';

                    try {
                        const response = await fetch(fetchUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ availability: this.availabilityPayload() })
                        });

                        if (!response.ok) {
                            const error = await response.json().catch(() => ({}));
                            throw new Error(error.message || 'Unable to save availability.');
                        }

                        const payload = await response.json();
                        this.loadAvailability(payload.availability || []);
                        this.statusMessage = 'Availability updated successfully.';
                    } catch (error) {
                        this.errorMessage = error.message || 'An unexpected error occurred.';
                    } finally {
                        this.saving = false;
                    }
                },

                get currentMonthLabel() {
                    return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
                },

                todayString() {
                    return this.formatDate(new Date());
                },

                formatDate(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }
            }));
        });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/partials/teacher.blade.php ENDPATH**/ ?>