<?php $__env->startSection('content'); ?>
<div class="space-y-8">
    <?php if(session('status')): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php
        $editing = $editingNotification ?? null;
        $channelSelections = collect(old('channels', $editing->channels ?? ['portal']));
        $selectedCourses = collect(old('course_ids', $editing ? $editing->courseTargets->pluck('course_id')->all() : []));
        $defaultTrigger = old('trigger_type', $editing->trigger_type ?? 'manual');
        $shouldOpenForm = $editing || old('title') || $errors->any();
    ?>
    <div x-data="{ formOpen: <?php echo e($shouldOpenForm ? 'true' : 'false'); ?>, triggerType: '<?php echo e($defaultTrigger); ?>' }" class="space-y-4">
        <div class="card p-6 space-y-6" x-show="formOpen" x-cloak>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-stone-900">Notifiche</h2>
                    <p class="text-sm text-stone-500">Crea messaggi preimpostati da inviare via email, WhatsApp o portale.</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <?php if(!$editing): ?>
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="formOpen = false">
                            Chiudi pannello
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($editing): ?>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 flex items-center justify-between">
                    <span>Stai modificando la notifica <strong><?php echo e($editing->title); ?></strong>.</span>
                    <a href="<?php echo e(route('notifications.index')); ?>" class="text-xs font-semibold text-amber-700 underline">Annulla</a>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('notifications.store')); ?>" class="grid grid-cols-1 gap-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="notification_id" value="<?php echo e(old('notification_id', $editing->id ?? '')); ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Titolo</label>
                    <input type="text" name="title" value="<?php echo e(old('title', $editing->title ?? '')); ?>" class="input-field mt-1" required>
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Trigger</label>
                    <select name="trigger_type" class="input-field mt-1" x-model="triggerType">
                        <option value="manual" <?php if($defaultTrigger === 'manual'): echo 'selected'; endif; ?>>Invio manuale</option>
                        <option value="event" <?php if($defaultTrigger === 'event'): echo 'selected'; endif; ?>>Evento</option>
                        <option value="scheduled" <?php if($defaultTrigger === 'scheduled'): echo 'selected'; endif; ?>>Programmata</option>
                    </select>
                </div>
                <div x-data="{
                        selected: '<?php echo e(old('event_type', $editing->event_type ?? '')); ?>',
                        options: <?php echo \Illuminate\Support\Js::from($eventOptions)->toHtml() ?>,
                        get current() {
                            return this.options.find(option => option.value === this.selected) || null;
                        }
                    }">
                    <label class="text-xs uppercase font-semibold text-stone-500">Evento collegato</label>
                    <select name="event_type" class="input-field mt-1" x-model="selected">
                        <?php $__currentLoopData = $eventOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option['value']); ?>" <?php if(old('event_type', $editing->event_type ?? '') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-stone-500 mt-1">Scegli il trigger solo se la notifica è di tipo evento (WhatsApp non verrà inviato).</p>
                    <?php $__errorArgs = ['event_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="mt-2 text-xs text-stone-500" x-show="selected === ''" x-cloak>
                        Segnaposto generici sempre disponibili: <code><?php echo e('{'); ?><?php echo e('user.name'); ?><?php echo e('}'); ?></code>, <code><?php echo e('{'); ?><?php echo e('user.email'); ?><?php echo e('}'); ?></code>, <code><?php echo e('{'); ?><?php echo e('course.title'); ?><?php echo e('}'); ?></code>, <code><?php echo e('{'); ?><?php echo e('payment.due_date'); ?><?php echo e('}'); ?></code>.
                    </div>
                    <template x-if="current && current.value">
                        <div class="mt-2 rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-600" x-cloak>
                            <p>
                                <span class="font-semibold text-stone-700">Segnaposto evento:</span>
                                <span x-text="current.placeholders.join(', ')"></span>
                            </p>
                            <p class="mt-1" x-text="current.hint"></p>
                        </div>
                    </template>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Canali</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <?php $__currentLoopData = ['portal' => 'Portale', 'email' => 'Email', 'whatsapp' => 'WhatsApp']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                                <input type="checkbox" name="channels[]" value="<?php echo e($key); ?>" class="rounded text-teal-600 border-stone-300" <?php if($channelSelections->contains($key)): echo 'checked'; endif; ?>>
                                <span><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="text-xs text-stone-500 mt-1">Seleziona almeno un canale di invio.</p>
                    <?php $__errorArgs = ['channels'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Messaggio</label>
                <textarea name="message_body" rows="4" class="input-field mt-1" placeholder="Testo della notifica"><?php echo e(old('message_body', $editing->message_body ?? '')); ?></textarea>
                <p class="text-xs text-stone-500 mt-1">Placeholder disponibili: <code><?php echo e('{'); ?><?php echo e('user.name'); ?><?php echo e('}'); ?></code>, <code><?php echo e('{'); ?><?php echo e('course.title'); ?><?php echo e('}'); ?></code>, <code><?php echo e('{'); ?><?php echo e('payment.due_date'); ?><?php echo e('}'); ?></code></p>
                <?php $__errorArgs = ['message_body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid md:grid-cols-2 gap-4" x-show="triggerType === 'scheduled'" x-cloak>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Frequenza programmata</label>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <input type="number" name="schedule_interval_value" min="1" max="365" value="<?php echo e(old('schedule_interval_value', $editing->schedule_interval_value ?? '')); ?>" class="input-field w-24" placeholder="Es. 2">
                        <select name="schedule_interval_unit" class="input-field">
                            <option value="">—</option>
                            <option value="hour" <?php if(old('schedule_interval_unit', $editing->schedule_interval_unit ?? '') === 'hour'): echo 'selected'; endif; ?>>Ore</option>
                            <option value="day" <?php if(old('schedule_interval_unit', $editing->schedule_interval_unit ?? '') === 'day'): echo 'selected'; endif; ?>>Giorni</option>
                            <option value="week" <?php if(old('schedule_interval_unit', $editing->schedule_interval_unit ?? '') === 'week'): echo 'selected'; endif; ?>>Settimane</option>
                            <option value="month" <?php if(old('schedule_interval_unit', $editing->schedule_interval_unit ?? '') === 'month'): echo 'selected'; endif; ?>>Mesi</option>
                        </select>
                        <input type="time" name="schedule_time" value="<?php echo e(old('schedule_time', $editing->schedule_time ?? '09:00')); ?>" class="input-field w-36">
                    </div>
                    <p class="text-xs text-stone-500 mt-1">Es.: “2 settimane alle 09:00” invia la notifica ogni 14 giorni all’orario scelto.</p>
                    <?php $__errorArgs = ['schedule_interval_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['schedule_interval_unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['schedule_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="text-xs text-stone-500">
                    <p class="font-semibold text-stone-600">Suggerimenti</p>
                    <p>Le notifiche programmate partono dall’orario indicato e continuano finché la notifica resta attiva. WhatsApp non viene inviato per i job programmati.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-xs uppercase font-semibold text-stone-500">Destinatari</p>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_teachers" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_teachers', (bool) ($editing->target_all_teachers ?? false))): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli insegnanti</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_clients" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_clients', (bool) ($editing->target_all_clients ?? false))): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli allievi</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_admins" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_admins', (bool) ($editing->target_all_admins ?? false))): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli amministratori</span>
                    </label>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Corsi</label>
                    <select name="course_ids[]" multiple class="input-field mt-1 h-32">
                        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>" <?php if($selectedCourses->contains($course->id)): echo 'selected'; endif; ?>><?php echo e($course->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-stone-500 mt-1">Seleziona i corsi interessati (max 20).</p>
                    <?php $__errorArgs = ['course_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="flex items-center justify-between flex-wrap gap-2">
                <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="send_now" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('send_now', true)): echo 'checked'; endif; ?>>
                    <span>Invia subito dopo il salvataggio</span>
                </label>
                <button type="submit" class="btn-primary"><?php echo e($editing ? 'Aggiorna notifica' : 'Salva notifica'); ?></button>
            </div>
        </form>
        </div>
        <div class="card p-6 space-y-4 text-center" x-show="!formOpen" x-cloak>
            <div>
                <h2 class="text-2xl font-semibold text-stone-900">Notifiche</h2>
                <p class="text-sm text-stone-500">Gestisci messaggi automatici. Apri il pannello per crearne uno nuovo.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" @click="formOpen = true">
                Crea nuova notifica
            </button>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <h3 class="text-xl font-semibold text-stone-900">Notifiche configurate</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-stone-200">
                <thead class="bg-stone-100 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Titolo</th>
                        <th class="px-3 py-2">Trigger</th>
                        <th class="px-3 py-2">Canali</th>
                        <th class="px-3 py-2">Destinatari</th>
                        <th class="px-3 py-2">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-semibold text-stone-800"><?php echo e($notification->title); ?></p>
                                <p class="text-xs text-stone-500"><?php echo e($notification->description); ?></p>
                            </td>
                            <td class="px-3 py-2 text-center text-xs">
                                <span class="inline-flex rounded-full bg-stone-100 px-2 py-0.5 font-semibold text-stone-600"><?php echo e(ucfirst($notification->trigger_type)); ?></span>
                            </td>
                            <td class="px-3 py-2 text-xs">
                                <?php echo e(implode(', ', $notification->channels ?? [])); ?>

                            </td>
                            <td class="px-3 py-2 text-xs">
                                <?php if($notification->target_all_teachers): ?>
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Docenti</span>
                                <?php endif; ?>
                                <?php if($notification->target_all_clients): ?>
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Allievi</span>
                                <?php endif; ?>
                                <?php if($notification->target_all_admins): ?>
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Amministratori</span>
                                <?php endif; ?>
                                <?php if($notification->courseTargets->isNotEmpty()): ?>
                                    <span class="inline-block bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full text-[11px]"><?php echo e($notification->courseTargets->count()); ?> corsi</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <?php if(!$notification->is_system && ($notification->created_by === $user->id || $user->role === 'Admin')): ?>
                                    <a href="<?php echo e(route('notifications.index', ['edit' => $notification->id])); ?>" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-[11px] font-semibold text-stone-600 hover:bg-stone-50 mr-2">Modifica</a>
                                <?php endif; ?>
                                <?php if($notification->trigger_type === 'manual' && ($notification->created_by === $user->id || $user->role === 'Admin')): ?>
                                    <form method="POST" action="<?php echo e(route('notifications.send', $notification)); ?>" onsubmit="return confirm('Inviare questa notifica?');" class="inline-flex mr-2">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-teal-200 px-3 py-1.5 text-[11px] font-semibold text-teal-600 hover:bg-teal-50">Invia ora</button>
                                    </form>
                                <?php endif; ?>
                                <?php if($notification->created_by === $user->id || $user->role === 'Admin'): ?>
                                    <form method="POST" action="<?php echo e(route('notifications.destroy', $notification)); ?>" onsubmit="return confirm('Eliminare questa notifica?');" class="inline-flex">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">Elimina</button>
                                    </form>
                                <?php endif; ?>
                                <?php if($notification->trigger_type === 'scheduled' && ($notification->created_by === $user->id || $user->role === 'Admin')): ?>
                                    <form method="POST" action="<?php echo e(route('notifications.toggle', $notification)); ?>" class="inline-flex ml-2">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-[11px] font-semibold <?php echo e($notification->is_active ? 'text-stone-600 hover:bg-stone-100' : 'text-emerald-600 border-emerald-200 hover:bg-emerald-50'); ?>">
                                            <?php echo e($notification->is_active ? 'Disattiva' : 'Attiva'); ?>

                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-stone-500 text-sm">Nessuna notifica configurata.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-xl font-semibold text-stone-900">Log invii notifiche</h3>
            <p class="text-sm text-stone-500">Scarica gli ultimi invii (fino a 200 record) in formato testo.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php if($canBroadcastAll): ?>
                <form method="POST" action="<?php echo e(route('notifications.jobs.purge')); ?>" onsubmit="return confirm('Svuotare il log degli invii?');" class="inline-flex">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100">Svuota log</button>
                </form>
            <?php endif; ?>
            <a href="<?php echo e(route('notifications.jobs.export')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                Scarica log invii
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/admin/notifications/index.blade.php ENDPATH**/ ?>