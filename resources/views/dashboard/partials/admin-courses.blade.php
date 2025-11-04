<div class="card p-6 space-y-6" x-data="{ showCreateCourse: false }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <h3 class="text-2xl font-semibold text-stone-900">Gestione corsi</h3>
            <p class="text-sm text-stone-500">Gestisci in modo rapido i corsi attivi e crea nuove sessioni.</p>
        </div>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-4 py-2 text-xs font-semibold text-teal-600 transition-colors hover:border-teal-300 hover:bg-teal-50"
            @click="showCreateCourse = !showCreateCourse"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span x-text="showCreateCourse ? 'Nascondi nuovo corso' : 'Nuovo corso'"></span>
        </button>
    </div>

    <div
        class="border border-teal-200/60 rounded-2xl bg-teal-50/60 p-6 shadow-inner"
        x-data="{
            schedule: [{ day: '', time: '' }],
            addSlot() { this.schedule.push({ day: '', time: '' }); },
            removeSlot(index) { if (this.schedule.length > 1) this.schedule.splice(index, 1); }
        }"
        x-cloak
        x-show="showCreateCourse"
        x-transition
    >
        <h4 class="text-lg font-semibold text-teal-800 mb-4">Crea nuovo corso</h4>
        <form method="POST" action="{{ route('admin.courses.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @csrf
            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Titolo</label>
                    <input type="text" name="title" required class="input-field text-sm" placeholder="Titolo del corso">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Docente</label>
                    <select name="teacher_id" required class="input-field text-sm">
                        <option value="" disabled selected>Seleziona un docente</option>
                        @foreach ($teacherOptions as $teacherId => $teacherName)
                            <option value="{{ $teacherId }}">{{ $teacherName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Prezzi abbonamenti (€)</label>
                    <p class="text-[11px] text-stone-500">Imposta 0 o lascia vuoto per nascondere l'opzione ai clienti.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <input type="number" step="0.01" name="monthly_price" class="input-field text-sm" placeholder="Mensile">
                        <input type="number" step="0.01" name="quarterly_price" class="input-field text-sm" placeholder="Trimestrale">
                        <input type="number" step="0.01" name="annual_price" class="input-field text-sm" placeholder="Annuale">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="space-y-1.5">
                        <label class="text-xs uppercase font-semibold text-stone-500">Data inizio</label>
                        <input type="date" name="start_date" required class="input-field text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs uppercase font-semibold text-stone-500">Data fine</label>
                        <input type="date" name="end_date" required class="input-field text-sm">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Specialità / focus</label>
                    <input type="text" name="speciality_description" class="input-field text-sm" placeholder="Es. Yoga dinamico">
                </div>
            </div>
            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Descrizione</label>
                    <textarea name="description" rows="5" class="input-field text-sm" placeholder="Descrizione sintetica del corso"></textarea>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs text-stone-600">
                        <span class="font-semibold uppercase tracking-wide">Orari settimanali</span>
                        <button type="button" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800" @click="addSlot()">+ Aggiungi</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(slot, index) in schedule" :key="index">
                            <div class="flex flex-wrap items-center gap-2 bg-white border border-stone-200 rounded-lg px-3 py-2">
                                <select name="schedule_day[]" class="input-field text-sm w-32" x-model="slot.day">
                                    <option value="">Giorno</option>
                                    @foreach ($dayOptions as $dayOption)
                                        <option value="{{ $dayOption }}">{{ $dayOption }}</option>
                                    @endforeach
                                </select>
                                <input type="time" name="schedule_time[]" class="input-field text-sm w-28" x-model="slot.time">
                                <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50" @click="removeSlot(index)">Rimuovi</button>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-xs">Salva nuovo corso</button>
                </div>
            </div>
        </form>
    </div>

    <div class="space-y-2">
        @forelse ($courses as $course)
            <div
                class="border border-stone-200 rounded-xl bg-white px-4 py-3 shadow-sm"
                x-data="{
                    open: false,
                    schedule: {{ Js::from($course['schedule']) }}.length ? {{ Js::from($course['schedule']) }} : [{ day: '', time: '' }],
                    addSlot() { this.schedule.push({ day: '', time: '' }); },
                    removeSlot(index) { if (this.schedule.length > 1) this.schedule.splice(index, 1); }
                }"
            >
                <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                        <span class="font-semibold text-stone-900">{{ $course['title'] }}</span>
                        <span class="text-xs text-stone-400">(#{{ $course['id'] }})</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <span class="font-semibold uppercase text-stone-600">Docente:</span>
                        <span>{{ $course['teacher_name'] ?? 'Non assegnato' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <span class="font-semibold uppercase text-stone-600">Periodo:</span>
                        <span>{{ $course['start_date_human'] ?? '—' }} → {{ $course['end_date_human'] ?? '—' }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1 text-xs text-stone-500">
                        @if(!empty($course['available_plans']))
                            @foreach ($course['available_plans'] as $plan)
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 font-semibold text-teal-700">
                                    {{ $plan['label'] }} · € {{ number_format($plan['amount'], 2, ',', '.') }}
                                </span>
                            @endforeach
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">
                                Nessun piano configurato
                            </span>
                        @endif
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1 text-[11px] font-semibold text-stone-600 hover:bg-stone-100" @click="open = !open">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span x-text="open ? 'Chiudi' : 'Gestisci'"></span>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-cloak x-transition class="mt-4 border-t border-stone-200 pt-4 space-y-4">
                    <form method="POST" action="{{ route('admin.courses.update', $course['id']) }}" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @csrf
                        @method('PUT')
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Titolo</label>
                                <input type="text" name="title" value="{{ $course['title'] }}" required class="input-field text-sm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Docente</label>
                                <select name="teacher_id" class="input-field text-sm">
                                    <option value="">Non assegnato</option>
                                    @foreach ($teacherOptions as $teacherId => $teacherName)
                                        <option value="{{ $teacherId }}" @selected($course['teacher_id'] === $teacherId)>{{ $teacherName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Prezzi abbonamenti (€)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="number" step="0.01" name="monthly_price" value="{{ number_format($course['monthly_price'] ?? $course['price'] ?? 0, 2, '.', '') }}" class="input-field text-sm" placeholder="Mensile">
                                    <input type="number" step="0.01" name="quarterly_price" value="{{ number_format($course['quarterly_price'] ?? 0, 2, '.', '') }}" class="input-field text-sm" placeholder="Trimestrale">
                                    <input type="number" step="0.01" name="annual_price" value="{{ number_format($course['annual_price'] ?? 0, 2, '.', '') }}" class="input-field text-sm" placeholder="Annuale">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1.5">
                                    <label class="text-xs uppercase font-semibold text-stone-500">Data inizio</label>
                                    <input type="date" name="start_date" value="{{ $course['start_date'] ?? '' }}" required class="input-field text-sm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs uppercase font-semibold text-stone-500">Data fine</label>
                                    <input type="date" name="end_date" value="{{ $course['end_date'] ?? '' }}" required class="input-field text-sm">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Specialità / focus</label>
                                <input type="text" name="speciality_description" value="{{ $course['speciality_description'] ?? '' }}" class="input-field text-sm">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Descrizione</label>
                                <textarea name="description" rows="4" class="input-field text-sm">{{ $course['description'] }}</textarea>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs text-stone-600">
                                    <span class="font-semibold uppercase tracking-wide">Orari settimanali</span>
                                    <button type="button" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800" @click="addSlot()">+ Aggiungi</button>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(slot, index) in schedule" :key="index">
                                        <div class="flex flex-wrap items-center gap-2 bg-stone-50 border border-stone-200 rounded-lg px-3 py-2">
                                            <select name="schedule_day[]" class="input-field text-sm w-32" x-model="slot.day">
                                                <option value="">Giorno</option>
                                                @foreach ($dayOptions as $dayOption)
                                                    <option value="{{ $dayOption }}">{{ $dayOption }}</option>
                                                @endforeach
                                            </select>
                                            <input type="time" name="schedule_time[]" class="input-field text-sm w-28" x-model="slot.time">
                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50" @click="removeSlot(index)">Rimuovi</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary text-xs">Salva modifiche</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-6 text-center text-stone-500">
                Nessun corso presente. Crea il primo corso utilizzando il modulo qui sopra.
            </div>
        @endforelse
    </div>
</div>
