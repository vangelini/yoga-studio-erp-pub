<?php
    $allowCourseCreation = $allowCourseCreation ?? true;
    $allowTeacherSelection = $allowTeacherSelection ?? true;
    $allowStudentManage = $allowStudentManage ?? true;
    $courseCardTitle = $courseCardTitle ?? 'Gestione corsi';
    $courseCardSubtitle = $courseCardSubtitle ?? 'I campi contrassegnati con <span class="text-rose-600 font-semibold">*</span> sono obbligatori.';
    $currentTeacherId = $currentTeacherId ?? null;
    $viewMode = $viewMode ?? 'admin';
    $isTeacherView = $viewMode === 'teacher';
    $currentTeacherName = auth()->user()->name ?? 'Insegnante';
    $planOptions = [
        'monthly' => 'Mensile',
        'quarterly' => 'Trimestrale',
        'annual' => 'Annuale',
    ];
?>

<div class="card p-6 space-y-6" x-data="{ showCreateCourse: false }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <h3 class="text-2xl font-semibold text-stone-900"><?php echo e($courseCardTitle); ?></h3>
            <p class="text-sm text-stone-500"><?php echo $courseCardSubtitle; ?></p>
        </div>
        <?php if($allowCourseCreation): ?>
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
        <?php endif; ?>
    </div>

    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Impossibile salvare il corso. Controlla i campi evidenziati e riprova.
        </div>
    <?php endif; ?>

    <?php if($allowCourseCreation): ?>
    <div
        class="border border-teal-200/60 rounded-2xl bg-teal-50/60 p-6 shadow-inner"
        x-data="{
            schedule: [{ day: '', time: '', capacity: '' }],
            pricingMode: '<?php echo e(old('pricing_mode', 'block')); ?>',
            addSlot() { this.schedule.push({ day: '', time: '', capacity: '' }); },
            removeSlot(index) { if (this.schedule.length > 1) this.schedule.splice(index, 1); },
            filledSlots() { return this.schedule.filter(slot => slot.day && slot.time).length || 0; },
            maxLessonOptions() { return Math.min(4, this.filledSlots()); }
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
                    <?php if($allowTeacherSelection): ?>
                        <select name="teacher_id" class="input-field text-sm">
                            <option value="" <?php echo e(old('teacher_id') ? '' : 'selected'); ?>>Non assegnato</option>
                            <?php $__currentLoopData = $teacherOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $teacherName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacherId); ?>" <?php if(old('teacher_id') == $teacherId): echo 'selected'; endif; ?>><?php echo e($teacherName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="teacher_id" value="<?php echo e($currentTeacherId); ?>">
                        <div class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                            <?php echo e(auth()->user()->name ?? 'Docente'); ?>

                        </div>
                    <?php endif; ?>
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
                    <label class="text-xs uppercase font-semibold text-stone-500">Modalità prezzo <span class="text-rose-600">*</span></label>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-stone-600">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="block" class="h-4 w-4 border-stone-300 text-teal-600" x-model="pricingMode" <?php if(old('pricing_mode', 'block') === 'block'): echo 'checked'; endif; ?>>
                            <span>Prezzo a blocco (mensile/trimestrale/annuale)</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="per_lesson" class="h-4 w-4 border-stone-300 text-teal-600" x-model="pricingMode" <?php if(old('pricing_mode') === 'per_lesson'): echo 'checked'; endif; ?>>
                            <span>Prezzo in base alle lezioni scelte</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-stone-500" x-show="pricingMode === 'per_lesson'">Imposta gli importi per ogni combinazione di piano e numero di lezioni selezionate.</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Prezzi abbonamenti (€)</label>
                    <p class="text-[11px] text-stone-500">Imposta 0 o lascia vuoto per nascondere l'opzione agli allievi.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" x-show="pricingMode === 'block'" x-cloak>
                        <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                            <span class="text-xs uppercase font-semibold text-stone-500 block">Mensile</span>
                            <input type="number" step="0.01" name="monthly_price" class="input-field text-sm w-full" value="<?php echo e(old('monthly_price')); ?>">
                        </label>
                        <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                            <span class="text-xs uppercase font-semibold text-stone-500 block">Trimestrale</span>
                            <input type="number" step="0.01" name="quarterly_price" class="input-field text-sm w-full" value="<?php echo e(old('quarterly_price')); ?>">
                        </label>
                        <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                            <span class="text-xs uppercase font-semibold text-stone-500 block">Annuale</span>
                            <input type="number" step="0.01" name="annual_price" class="input-field text-sm w-full" value="<?php echo e(old('annual_price')); ?>">
                        </label>
                    </div>
                    <div class="space-y-2" x-show="pricingMode === 'per_lesson'" x-cloak>
                        <?php $__currentLoopData = $planOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planKey => $planLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-lg border border-stone-200 bg-white px-3 py-2">
                                <p class="text-xs font-semibold text-stone-600"><?php echo e($planLabel); ?></p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                                    <?php for($lessons = 1; $lessons <= 4; $lessons++): ?>
                                        <label class="text-[11px] text-stone-500 space-y-1" x-show="<?php echo e($lessons); ?> <= maxLessonOptions()" x-cloak>
                                            <span><?php echo e($lessons); ?> lezioni/settimana</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="lesson_pricing[<?php echo e($planKey); ?>][<?php echo e($lessons); ?>]"
                                                value="<?php echo e(old('lesson_pricing.'.$planKey.'.'.$lessons)); ?>"
                                                class="input-field text-xs"
                                                placeholder="€"
                                            >
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-[11px] text-stone-400" x-text="`Basato su ${maxLessonOptions()} giorno/i configurati in calendario.`"></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-semibold text-stone-500">Capienza massima corso</label>
                    <input type="number" min="1" name="max_enrollments" value="<?php echo e(old('max_enrollments')); ?>" class="input-field text-sm" placeholder="Es. 20">
                    <p class="text-[11px] text-stone-500">Una volta raggiunta la capienza, le nuove iscrizioni verranno bloccate automaticamente.</p>
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
                        <p class="text-xs text-stone-500">Se selezionato, questo corso potrà essere scelto dagli Allievi come lezione extra.</p>
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
                                <input type="number" name="schedule_capacity[]" min="1" class="input-field text-sm w-28" placeholder="Capienza" x-model="slot.capacity">
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
    <?php endif; ?>

    </div>

    <div class="space-y-2">
        <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div
                class="border border-stone-200 rounded-xl bg-white px-4 py-3 shadow-sm"
                x-data="{
                    open: false,
                    pricingMode: '<?php echo e($course['pricing_mode'] ?? 'block'); ?>',
                    schedule: <?php echo e(Js::from($course['schedule'])); ?>.length ? <?php echo e(Js::from($course['schedule'])); ?> : [{ day: '', time: '', capacity: '' }],
                    addSlot() { this.schedule.push({ day: '', time: '', capacity: '' }); },
                    removeSlot(index) { if (this.schedule.length > 1) this.schedule.splice(index, 1); },
                    filledSlots() { return this.schedule.filter(slot => slot.day && slot.time).length || 0; },
                    maxLessonOptions() { return Math.min(4, this.filledSlots()); }
                }"
            >
                <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                        <span class="font-semibold text-stone-900"><?php echo e($course['title']); ?></span>
                        <span class="text-xs text-stone-400">(#<?php echo e($course['id']); ?>)</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <span class="font-semibold uppercase text-stone-600">Insegnante:</span>
                        <span>
                            <?php echo e($course['teacher_name'] ?? ($isTeacherView ? $currentTeacherName : 'Non assegnato')); ?>

                        </span>
                    </div>
                    <?php if(!empty($course['start_date_human']) || !empty($course['end_date_human'])): ?>
                        <div class="flex items-center gap-2 text-xs text-stone-500">
                            <span class="font-semibold uppercase text-stone-600">Periodo:</span>
                            <span><?php echo e($course['start_date_human'] ?? '—'); ?> → <?php echo e($course['end_date_human'] ?? '—'); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center gap-1 text-xs text-stone-500">
                        <?php
                            $plans = $course['available_plans'] ?? $course['availablePlans'] ?? [];
                            $pricingMode = $course['pricing_mode'] ?? 'block';
                        ?>
                        <?php if($pricingMode === 'per_lesson'): ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700">
                                Tariffazione per lezioni scelte
                            </span>
                        <?php elseif(!empty($plans)): ?>
                            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 font-semibold text-teal-700">
                                    <?php echo e($plan['label']); ?> · € <?php echo e(number_format($plan['amount'], 2, ',', '.')); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 font-semibold text-stone-500">
                                Tariffe non configurate
                            </span>
                        <?php endif; ?>
                        <?php if(!empty($course['max_enrollments'])): ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 font-semibold text-stone-500">
                                Capienza max: <?php echo e($course['max_enrollments']); ?> allievi
                            </span>
                        <?php endif; ?>
                    </div>
                        <button
                            type="button"
                            class=" flex justify-end items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                            @click="open = !open"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span x-text="open ? 'Nascondi dettagli corso' : 'Mostra dettagli corso'"></span>
                        </button>
                </div>

                

                <div x-show="open" x-cloak x-transition class="mt-4 border border-stone-200 rounded-xl bg-stone-50 px-4 py-4 space-y-4">
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
                                <?php if($allowTeacherSelection): ?>
                                    <select name="teacher_id" class="input-field text-sm">
                                        <option value="">Non assegnato</option>
                                        <?php $__currentLoopData = $teacherOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $teacherName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($teacherId); ?>" <?php if($course['teacher_id'] === $teacherId): echo 'selected'; endif; ?>><?php echo e($teacherName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php else: ?>
                                    <input type="hidden" name="teacher_id" value="<?php echo e($currentTeacherId); ?>">
                                    <div class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                        <?php echo e(auth()->user()->name ?? 'Docente'); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Modalità prezzo</label>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-stone-600">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" name="pricing_mode" value="block" class="h-4 w-4 border-stone-300 text-teal-600" x-model="pricingMode" <?php if(($course['pricing_mode'] ?? 'block') === 'block'): echo 'checked'; endif; ?>>
                                        <span>Prezzo a blocco</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" name="pricing_mode" value="per_lesson" class="h-4 w-4 border-stone-300 text-teal-600" x-model="pricingMode" <?php if(($course['pricing_mode'] ?? 'block') === 'per_lesson'): echo 'checked'; endif; ?>>
                                        <span>Prezzo per numero di lezioni</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Prezzi abbonamenti (€)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" x-show="pricingMode === 'block'" x-cloak>
                                    <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                                        <span class="text-xs uppercase font-semibold text-stone-500 block">Mensile</span>
                                        <input type="number" step="0.01" name="monthly_price" value="<?php echo e(number_format($course['monthly_price'] ?? $course['price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm w-full" placeholder="Mensile">
                                    </label>
                                    <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                                        <span class="text-xs uppercase font-semibold text-stone-500 block">Trimestrale</span>
                                        <input type="number" step="0.01" name="quarterly_price" value="<?php echo e(number_format($course['quarterly_price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm w-full" placeholder="Trimestrale">
                                    </label>
                                    <label class="flex flex-col gap-1 text-sm text-stone-600 w-full">
                                        <span class="text-xs uppercase font-semibold text-stone-500 block">Annuale</span>
                                        <input type="number" step="0.01" name="annual_price" value="<?php echo e(number_format($course['annual_price'] ?? 0, 2, '.', '')); ?>" class="input-field text-sm w-full" placeholder="Annuale">
                                    </label>
                                </div>
                                <div class="space-y-2" x-show="pricingMode === 'per_lesson'" x-cloak>
                                    <?php $__currentLoopData = $planOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planKey => $planLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $planPricing = $course['lesson_pricing'][$planKey] ?? []; ?>
                                        <div class="rounded-lg border border-stone-200 bg-white px-3 py-2">
                                            <p class="text-xs font-semibold text-stone-600"><?php echo e($planLabel); ?></p>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                                                <?php for($lessons = 1; $lessons <= 4; $lessons++): ?>
                                                    <label class="text-[11px] text-stone-500 space-y-1" x-show="<?php echo e($lessons); ?> <= maxLessonOptions()" x-cloak>
                                                        <span><?php echo e($lessons); ?> lezioni/settimana</span>
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            name="lesson_pricing[<?php echo e($planKey); ?>][<?php echo e($lessons); ?>]"
                                                            value="<?php echo e($planPricing[$lessons] ?? ''); ?>"
                                                            class="input-field text-xs"
                                                            placeholder="€"
                                                        >
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="text-[11px] text-stone-400" x-text="maxLessonOptions() > 0 ? `Basato su ${maxLessonOptions()} giorno/i configurati in calendario.` : 'Configura almeno un giorno nel calendario per definire i prezzi.'"></p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs uppercase font-semibold text-stone-500">Capienza massima corso</label>
                                <input type="number" min="1" name="max_enrollments" value="<?php echo e($course['max_enrollments']); ?>" class="input-field text-sm" placeholder="Es. 20">
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
                                    <p class="text-xs text-stone-500">Consente agli allievi di scegliere una lezione settimanale extra da questo corso.</p>
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
                                <input type="number" name="schedule_capacity[]" min="1" class="input-field text-sm w-28" placeholder="Capienza" x-model="slot.capacity">
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

            <div class="mt-6 space-y-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-stone-700">Allieve/i iscritti</p>
                    </div>
                    <span class="text-xs font-semibold text-stone-500"><?php echo e(count($course['students'] ?? [])); ?> iscritti</span>
                </div>

                <?php if(!empty($course['students'])): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $course['students']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-col gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 shadow-sm md:flex-row md:items-center md:justify-between">
                                <div>
                                    <a href="<?php echo e(route('admin.clients.index', ['client_id' => $student['client_id']])); ?>#client-<?php echo e($student['client_id']); ?>" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
                                        <?php echo e($student['name']); ?>

                                    </a>
                                    <p class="text-xs text-stone-500">Piano: <?php echo e($student['plan']); ?></p>
                                    <?php if(!empty($student['lessons'])): ?>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <?php $__currentLoopData = $student['lessons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 text-[11px] font-semibold text-stone-600">
                                                    <?php echo e($lesson['label'] ?? trim(($lesson['day'] ?? '') . ' ' . ($lesson['time'] ?? ''))); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-stone-500 space-y-1">
                                    <?php if(!empty($student['email'])): ?>
                                        <a href="mailto:<?php echo e($student['email']); ?>" class="inline-flex items-center gap-1 text-teal-600 font-semibold hover:text-teal-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4 4m0 0l-4-4m4 4V8m-6 4V7a2 2 0 012-2h8a2 2 0 012 2v5"/>
                                            </svg>
                                            <?php echo e($student['email']); ?>

                                        </a>
                                    <?php endif; ?>
                                    <?php if(!empty($student['telephone'])): ?>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-stone-600">Telefono:</span>
                                            <?php if(!empty($student['whatsapp'])): ?>
                                                <a href="<?php echo e($student['whatsapp']); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-emerald-600 font-semibold hover:text-emerald-800">
                                                    <?php echo e($student['telephone']); ?>

                                                </a>
                                            <?php else: ?>
                                                <span><?php echo e($student['telephone']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo e($student['status_badge']); ?>">
                                        <?php echo e($student['status']); ?>

                                    </span>
                                    <?php if($allowStudentManage): ?>
                                        <a href="<?php echo e(route('admin.clients.index', ['client_id' => $student['client_id']])); ?>#client-<?php echo e($student['client_id']); ?>" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100">
                                            Gestisci
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-stone-500">Nessun allievo iscritto a questo corso.</p>
                <?php endif; ?>
            </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 px-6 py-6 text-center text-stone-500">
                Nessun corso presente. Crea il primo corso utilizzando il modulo qui sopra.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\yoga-studio-erp\resources\views/dashboard/partials/admin-courses.blade.php ENDPATH**/ ?>