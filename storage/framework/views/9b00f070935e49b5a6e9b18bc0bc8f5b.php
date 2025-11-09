<div class="card p-6 space-y-6" x-data="{ showCreateCourse: false }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <h3 class="text-2xl font-semibold text-stone-900">Gestione corsi</h3>
            <p class="text-sm text-stone-500">I campi contrassegnati con <span class="text-rose-600 font-semibold">*</span> sono obbligatori.</p>
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

    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Impossibile salvare il corso. Controlla i campi evidenziati e riprova.
        </div>
    <?php endif; ?>

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
        <form method="POST" action="<?php echo e(route('admin.courses.store')); ?>" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <?php echo csrf_field(); ?>
            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Titolo <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" value="<?php echo e(old('title')); ?>" required class="input-field text-sm" placeholder="Titolo del corso">
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Insegnante (opzionale)</label>
                    <select name="teacher_id" class="input-field text-sm">
                        <option value="" <?php echo e(old('teacher_id') ? '' : 'selected'); ?>>Non assegnato</option>
                        <?php $__currentLoopData = $teacherOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $teacherName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacherId); ?>" <?php if(old('teacher_id') == $teacherId): echo 'selected'; endif; ?>><?php echo e($teacherName); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                        <label class="text-xs uppercase font-semibold text-stone-500">Data inizio <span class="text-rose-600">*</span></label>
                        <input type="date" name="start_date" value="<?php echo e(old('start_date')); ?>" required class="input-field text-sm">
                        <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs uppercase font-semibold text-stone-500">Data fine <span class="text-rose-600">*</span></label>
                        <input type="date" name="end_date" value="<?php echo e(old('end_date')); ?>" required class="input-field text-sm">
                        <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Specialità / focus</label>
                    <input type="text" name="speciality_description" class="input-field text-sm" placeholder="Es. Yoga dinamico">
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-teal-100 bg-white px-3 py-2">
                    <input type="checkbox" name="allows_extra_day" value="1" class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                    <div>
                        <p class="text-sm font-semibold text-stone-700">Disponibile come “Un giorno in più”</p>
                        <p class="text-xs text-stone-500">Se selezionato, questo corso potrà essere scelto dai clienti come lezione extra.</p>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Sconto “Un giorno in più” (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="extra_day_discount_percent" value="<?php echo e(old('extra_day_discount_percent', 0)); ?>" class="input-field text-sm" placeholder="Es. 10">
                    <p class="text-[11px] text-stone-500">Percentuale applicata sul costo extra prima della riduzione per lezioni rimanenti.</p>
                    <?php $__errorArgs = ['extra_day_discount_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="space-y-3">
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Descrizione <span class="text-rose-600">*</span></label>
                    <textarea name="description" rows="5" class="input-field text-sm" placeholder="Descrizione sintetica del corso" required><?php echo e(old('description')); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                    <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($dayOption); ?>"><?php echo e($dayOption); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div
                class="border border-stone-200 rounded-xl bg-white px-4 py-3 shadow-sm"
                x-data="{
                    open: false,
                    schedule: <?php echo e(Js::from($course['schedule'])); ?>.length ? <?php echo e(Js::from($course['schedule'])); ?> : [{ day: '', time: '' }],
                    addSlot() { this.schedule.push({ day: '', time: '' }); },
                    removeSlot(index) { if (this.schedule.length > 1) this.schedule.splice(index, 1); }
                }"
            >
                <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                        <span class="font-semibold text-stone-900"><?php echo e($course['title']); ?></span>
                        <span class="text-xs text-stone-400">(#<?php echo e($course['id']); ?>)</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <span class="font-semibold uppercase text-stone-600">Insegnante:</span>
                        <span><?php echo e($course['teacher_name'] ?? 'Non assegnato'); ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <span class="font-semibold uppercase text-stone-600">Periodo:</span>
                        <span><?php echo e($course['start_date_human'] ?? '—'); ?> → <?php echo e($course['end_date_human'] ?? '—'); ?></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1 text-xs text-stone-500">
                        <?php if(!empty($course['available_plans'])): ?>
                            <?php $__currentLoopData = $course['available_plans']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 font-semibold text-teal-700">
                                    <?php echo e($plan['label']); ?> · € <?php echo e(number_format($plan['amount'], 2, ',', '.')); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">
                                Nessun piano configurato
                            </span>
                        <?php endif; ?>
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
                    <form method="POST" action="<?php echo e(route('admin.courses.update', $course['id'])); ?>" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Titolo <span class="text-rose-600">*</span></label>
                                <input type="text" name="title" value="<?php echo e($course['title']); ?>" required class="input-field text-sm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Insegnante (opzionale)</label>
                                <select name="teacher_id" class="input-field text-sm">
                                    <option value="">Non assegnato</option>
                                    <?php $__currentLoopData = $teacherOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $teacherName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacherId); ?>" <?php if($course['teacher_id'] === $teacherId): echo 'selected'; endif; ?>><?php echo e($teacherName); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Prezzi abbonamenti (€)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="number" step="0.01" name="monthly_price" value="<?php echo e(number_format($course['monthly_price'] ?? $course['price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm" placeholder="Mensile">
                                    <input type="number" step="0.01" name="quarterly_price" value="<?php echo e(number_format($course['quarterly_price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm" placeholder="Trimestrale">
                                    <input type="number" step="0.01" name="annual_price" value="<?php echo e(number_format($course['annual_price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm" placeholder="Annuale">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1.5">
                                    <label class="text-xs uppercase font-semibold text-stone-500">Data inizio <span class="text-rose-600">*</span></label>
                                    <input type="date" name="start_date" value="<?php echo e($course['start_date'] ?? ''); ?>" required class="input-field text-sm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs uppercase font-semibold text-stone-500">Data fine <span class="text-rose-600">*</span></label>
                                    <input type="date" name="end_date" value="<?php echo e($course['end_date'] ?? ''); ?>" required class="input-field text-sm">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Specialità / focus</label>
                                <input type="text" name="speciality_description" value="<?php echo e($course['speciality_description'] ?? ''); ?>" class="input-field text-sm">
                            </div>
                            <div class="flex items-start gap-3 rounded-xl border border-teal-100 bg-white px-3 py-2">
                                <input type="checkbox" name="allows_extra_day" value="1" class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-500" <?php if($course['allows_extra_day'] ?? false): echo 'checked'; endif; ?>>
                                <div>
                                    <p class="text-sm font-semibold text-stone-700">Disponibile come “Un giorno in più”</p>
                                    <p class="text-xs text-stone-500">Consente ai clienti di scegliere una lezione settimanale extra da questo corso.</p>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Sconto “Un giorno in più” (%)</label>
                                <input type="number" step="0.1" min="0" max="100" name="extra_day_discount_percent" value="<?php echo e(number_format($course['extra_day_discount_percent'] ?? 0, 1, '.', '')); ?>" class="input-field text-sm">
                                <p class="text-[11px] text-stone-500">Applicato sul costo extra prima del calcolo delle lezioni rimanenti.</p>
                                <?php $__errorArgs = ['extra_day_discount_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Descrizione <span class="text-rose-600">*</span></label>
                                <textarea name="description" rows="4" class="input-field text-sm" required><?php echo e($course['description']); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-rose-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                                <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($dayOption); ?>"><?php echo e($dayOption); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-6 text-center text-stone-500">
                Nessun corso presente. Crea il primo corso utilizzando il modulo qui sopra.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/partials/admin-courses.blade.php ENDPATH**/ ?>