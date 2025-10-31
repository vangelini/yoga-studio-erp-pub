<div class="card p-6 space-y-6" x-data="{ showCreateCourse: false }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <h3 class="text-2xl font-semibold text-stone-900">Gestione corsi</h3>
            <p class="text-sm text-stone-500">Crea nuovi corsi e modifica quelli esistenti, inclusi orari e docenti.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-4 py-2 text-xs font-semibold text-teal-600 transition-colors hover:border-teal-300 hover:bg-teal-50"
                @click="showCreateCourse = !showCreateCourse"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span x-text="showCreateCourse ? 'Nascondi corso' : 'Nuovo corso'"></span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
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
            <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-stone-600">Titolo</label>
                    <input type="text" name="title" required class="w-full p-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Titolo del corso">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-stone-600">Docente</label>
                    <select name="teacher_id" required class="w-full p-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <option value="" disabled selected>Seleziona un docente</option>
                        @foreach ($teacherOptions as $teacherId => $teacherName)
                            <option value="{{ $teacherId }}">{{ $teacherName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-stone-600">Prezzo (€)</label>
                    <input type="number" step="0.01" name="price" required class="w-full p-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="0.00">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-stone-600">Descrizione</label>
                    <textarea name="description" rows="3" required class="w-full p-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Descrivi il corso..."></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-stone-600">Specialità/Focus</label>
                    <textarea name="speciality_description" rows="2" class="w-full p-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Es. Yoga dinamico, meditazione..."></textarea>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-stone-600">Orari settimanali</label>
                        <button type="button" class="text-xs font-semibold text-teal-600 hover:text-teal-800" @click="addSlot()">+ Aggiungi orario</button>
                    </div>
                    <template x-for="(slot, index) in schedule" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-white border border-stone-200 rounded-xl p-3">
                            <select class="p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" name="schedule_day[]" x-model="slot.day">
                                <option value="">Giorno</option>
                                @foreach ($dayOptions as $dayOption)
                                    <option value="{{ $dayOption }}">{{ $dayOption }}</option>
                                @endforeach
                            </select>
                            <input type="time" name="schedule_time[]" x-model="slot.time" class="p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <button type="button" class="text-xs text-rose-600 font-semibold border border-rose-200 rounded-lg px-3 py-2 hover:bg-rose-50 transition" @click="removeSlot(index)">Rimuovi</button>
                        </div>
                    </template>
                </div>
                <button type="submit" class="btn-primary w-full justify-center text-sm">Salva nuovo corso</button>
            </form>
        </div>

        <div class="space-y-5">
            @forelse ($courses as $course)
                <div
                    class="border border-stone-200 rounded-2xl p-5 bg-white shadow-sm"
                    x-data="{ open: false, schedule: {{ Js::from($course['schedule']) }} }"
                >
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 bg-teal-100 text-teal-700 px-3 py-1.5 rounded-full text-xs font-semibold">
                                Corso
                            </div>
                            <h4 class="text-xl font-semibold text-stone-900">{{ $course['title'] }}</h4>
                            <p class="text-sm text-stone-500">Docente: {{ $course['teacher_name'] ?? 'Da assegnare' }}</p>
                            <p class="text-sm text-stone-600 leading-relaxed">{{ $course['description'] }}</p>
                        </div>
                        <div class="text-sm text-stone-500 bg-stone-100 rounded-xl border border-stone-200 px-4 py-3">
                            <p class="font-semibold text-stone-700 mb-1">Orari</p>
                            <ul class="space-y-1">
                                @forelse ($course['schedule'] as $slot)
                                    <li>{{ $slot['day'] }} · {{ $slot['time'] }}</li>
                                @empty
                                    <li>Nessun orario impostato.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="text-xs font-semibold inline-flex items-center gap-2 bg-stone-200 text-stone-700 px-3 py-2 rounded-lg hover:bg-stone-300 transition-colors" @click="open = !open">
                            <span x-text="open ? 'Chiudi modifica' : 'Modifica corso'"></span>
                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="open" x-transition class="mt-5 border-t border-stone-200 pt-5">
                        <form method="POST" action="{{ route('admin.courses.update', $course['id']) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase text-stone-500">Titolo</label>
                                    <input type="text" name="title" value="{{ $course['title'] }}" required class="w-full p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase text-stone-500">Docente</label>
                                    <select name="teacher_id" required class="w-full p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                        @foreach ($teacherOptions as $teacherId => $teacherName)
                                            <option value="{{ $teacherId }}" @selected($course['teacher_id'] ?? null === $teacherId)>{{ $teacherName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase text-stone-500">Prezzo (€)</label>
                                    <input type="number" step="0.01" name="price" value="{{ number_format($course['price'] ?? 0, 2, '.', '') }}" required class="w-full p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase text-stone-500">Specialità/Focus</label>
                                    <input type="text" name="speciality_description" value="{{ $course['speciality_description'] ?? '' }}" class="w-full p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase text-stone-500">Descrizione</label>
                                <textarea name="description" rows="3" class="w-full p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">{{ $course['description'] }}</textarea>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold uppercase text-stone-500">Orari settimanali</label>
                                    <button type="button" class="text-xs font-semibold text-teal-600 hover:text-teal-800" @click="schedule.push({ day: '', time: '' })">+ Aggiungi orario</button>
                                </div>
                                <template x-for="(slot, index) in schedule" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-stone-50 border border-stone-200 rounded-xl p-3">
                                        <select class="p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" name="schedule_day[]" x-model="slot.day">
                                            <option value="">Giorno</option>
                                            @foreach ($dayOptions as $dayOption)
                                                <option value="{{ $dayOption }}">{{ $dayOption }}</option>
                                            @endforeach
                                        </select>
                                        <input type="time" name="schedule_time[]" x-model="slot.time" class="p-2.5 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                        <button type="button" class="text-xs text-rose-600 font-semibold border border-rose-200 rounded-lg px-3 py-2 hover:bg-rose-50 transition" @click="schedule.splice(index, 1)">Rimuovi</button>
                                    </div>
                                </template>
                            </div>
                            <button type="submit" class="btn-primary text-xs">Aggiorna corso</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center text-stone-500">
                    Nessun corso presente. Crea il primo corso utilizzando il modulo a sinistra.
                </div>
            @endforelse
        </div>
    </div>
</div>
