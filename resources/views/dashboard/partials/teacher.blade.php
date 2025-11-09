@php
    $activeTeacher = $teachers->firstWhere('id', auth()->id());
    $teacherReceipts = collect($receipts ?? []);
@endphp

<section class="space-y-10">
    <div
        x-data="teacherAvailability({
            initialAvailability: @json($activeTeacher['availability'] ?? []),
            fetchUrl: '{{ url('/api/teachers/' . auth()->id() . '/availability') }}'
        })"
        x-init="init()"
        class="bg-white rounded-xl shadow-sm border border-stone-200 p-6"
    >
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-2xl font-semibold text-stone-800">Manage Availability</h3>
                <p class="text-sm text-stone-500">Click a time slot to add or remove your availability. Booked slots cannot be changed.</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="bg-stone-200 text-stone-700 px-4 py-2 rounded-lg hover:bg-stone-300 transition-colors"
                    @click="prevMonth"
                >
                    Previous
                </button>
                <div class="text-center">
                    <div class="text-xs uppercase tracking-wider text-stone-400">Month</div>
                    <div class="text-lg font-semibold text-stone-700" x-text="currentMonthLabel"></div>
                </div>
                <button
                    type="button"
                    class="bg-stone-200 text-stone-700 px-4 py-2 rounded-lg hover:bg-stone-300 transition-colors"
                    @click="nextMonth"
                >
                    Next
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-2 text-center text-xs md:text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">
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
                                        Booked by <span class="font-semibold" x-text="booked[slotKey(day.iso, time)].bookedByName ?? 'Client'"></span>
                                    </span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-6 flex flex-wrap gap-4 text-xs text-stone-600 border-t border-stone-200 pt-4">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-stone-100 border"></span>
                <span>Unavailable</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-blue-500"></span>
                <span>Available (saved)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-green-500"></span>
                <span>Will be added</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-rose-200 border"></span>
                <span>Will be removed</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-red-600"></span>
                <span>Booked</span>
            </div>
        </div>

        <div class="mt-6 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div class="text-sm">
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
                    Reset
                </button>
                <button
                    type="button"
                    class="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-teal-400 disabled:cursor-not-allowed"
                    @click="saveChanges"
                    :disabled="!isDirty() || saving"
                >
                    <span x-show="!saving">Save Changes</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h3 class="text-2xl font-semibold text-stone-800">Ricevute lezioni private</h3>
                <p class="text-sm text-stone-500">Scarica le ricevute dei pagamenti registrati dai tuoi clienti.</p>
            </div>
            <span class="text-xs uppercase tracking-widest text-stone-400">Ultime 10</span>
        </div>
        <div class="space-y-3">
            @forelse ($teacherReceipts as $receipt)
                <div class="border border-stone-200 rounded-lg px-4 py-3 bg-stone-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-stone-800">{{ $receipt['client_name'] ?? 'Cliente' }}</p>
                        <p class="text-xs text-stone-500">
                            @if (!empty($receipt['lesson_date']))
                                Lezione del {{ $receipt['lesson_date'] }}@if (!empty($receipt['lesson_time'])) alle {{ $receipt['lesson_time'] }}@endif
                            @else
                                Ricevuta disponibile
                            @endif
                        </p>
                        @if (!empty($receipt['paid_at']))
                            <p class="text-[11px] text-stone-400">Pagata il {{ $receipt['paid_at'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-base font-semibold text-stone-800">€ {{ $receipt['amount_formatted'] ?? number_format($receipt['amount'] ?? 0, 2, ',', '.') }}</span>
                        @if (!empty($receipt['receipt_route']))
                            <a
                                href="{{ $receipt['receipt_route'] }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-2 rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-teal-50 transition"
                            >
                                Scarica
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500">Non ci sono ricevute disponibili al momento.</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 lg:col-span-2">
            <h3 class="text-2xl font-semibold text-stone-800 mb-4">Upcoming Lessons</h3>
            <div class="space-y-4">
                @forelse ($bookings as $booking)
                    <div class="p-4 bg-stone-50 rounded-lg flex justify-between items-center">
                        <div>
                            <p class="text-lg font-semibold text-teal-700">
                                {{ $booking['date'] ? \Carbon\Carbon::parse($booking['date'])->format('l, F j') : 'Date to be confirmed' }}
                            </p>
                            <p class="text-stone-600 text-sm">
                                {{ $booking['time'] ?? 'Time to be confirmed' }} · with {{ optional($booking['client'])->name ?? 'Client' }}
                            </p>
                        </div>
                        @php($contactEmail = optional($booking['client'])->email)
                        <a
                            href="{{ $contactEmail ? 'mailto:' . $contactEmail : '#' }}"
                            class="text-teal-600 text-sm font-semibold hover:text-teal-800 underline {{ $contactEmail ? '' : 'pointer-events-none opacity-50' }}"
                        >
                            Contact
                        </a>
                    </div>
                @empty
                    <p class="text-stone-500 text-sm">You do not have any upcoming lessons yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
            <h3 class="text-2xl font-semibold text-stone-800 mb-4">Your Students</h3>
            <ul class="space-y-3">
                @forelse ($clients as $client)
                    <li class="p-3 bg-stone-50 rounded-lg">
                        <p class="font-semibold text-stone-800">{{ $client->name }}</p>
                        @php($studentWhatsapp = preg_replace('/\D+/', '', $client->telephone ?? ''))
                        @if ($client->email)
                            <p class="text-xs mt-1">
                                <a href="mailto:{{ $client->email }}" class="text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                    {{ $client->email }}
                                </a>
                            </p>
                        @endif
                        <p class="text-xs text-stone-500 mt-1">Status: {{ ucfirst($client->status) }}</p>
                        @if ($client->telephone)
                            <p class="text-xs mt-1">
                                @if ($studentWhatsapp)
                                    <a href="https://wa.me/{{ $studentWhatsapp }}" target="_blank" rel="noopener" class="text-teal-600 font-semibold hover:text-teal-800 underline decoration-dotted">
                                        {{ $client->telephone }}
                                    </a>
                                @else
                                    <span class="text-stone-500">{{ $client->telephone }}</span>
                                @endif
                            </p>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-stone-500">No students yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
        <h3 class="text-2xl font-semibold text-stone-800 mb-6">Teacher Directory</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($teachers as $teacher)
                <div class="p-4 bg-stone-50 rounded-xl border border-stone-200">
                    <div class="flex items-center gap-3">
                        <img src="{{ $teacher['profile_picture_url'] }}" alt="{{ $teacher['name'] }}" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <p class="text-lg font-semibold text-stone-800">{{ $teacher['name'] }}</p>
                            <p class="text-xs text-stone-500">Specialties: {{ implode(', ', $teacher['specializations']) ?: 'TBA' }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-stone-600 mt-3">{{ Str::limit($teacher['bio'], 120) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
            Alpine.data('teacherAvailability', ({ initialAvailability, fetchUrl }) => ({
                weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
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
    @endpush
@endonce

