<?php
    $activeTeacher = $teachers->firstWhere('id', auth()->id());
    $teacherReceipts = collect($receipts ?? []);
?>

<section class="space-y-10">
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

            <div class="flex flex-wrap gap-4 text-xs text-stone-600 border-t border-stone-200 pt-4">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-stone-100 border"></span>
                    <span>Non disponibile</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-blue-500"></span>
                    <span>Disponibile (salvato)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-green-500"></span>
                    <span>In attesa di salvataggio</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-rose-200 border"></span>
                    <span>Verrà rimosso</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-red-600"></span>
                    <span>Prenotato</span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                <div class="text-sm space-y-1">
                    <p class="text-stone-500">Ricordati di confermare i cambi cliccando su “Salva”.</p>
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

    <?php if($private_lessons_enabled ?? false): ?>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
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
                    <p class="text-stone-500 text-sm">You do not have any upcoming lessons yet.</p>
                <?php endif; ?>
            </div>
        </div>

       
    </div>
    <?php endif; ?>

   
</section>

<?php if (! $__env->hasRenderedOnce('852ccab8-228d-4efb-9be3-b0799628f536')): $__env->markAsRenderedOnce('852ccab8-228d-4efb-9be3-b0799628f536'); ?>
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