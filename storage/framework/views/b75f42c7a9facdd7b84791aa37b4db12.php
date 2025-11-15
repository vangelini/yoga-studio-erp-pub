<?php $__env->startSection('content'); ?>
<div class="space-y-8">
    <?php if(session('status')): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <div class="card p-6 space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-stone-900">Notifiche</h2>
            <p class="text-sm text-stone-500">Crea messaggi preimpostati da inviare via email, WhatsApp o portale.</p>
            <?php if($canBroadcastAll): ?>
                <form method="POST" action="<?php echo e(route('notifications.pending.resend')); ?>" class="mt-3 inline-flex">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">
                        Reinvia notifica pendenze
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <form method="POST" action="<?php echo e(route('notifications.store')); ?>" class="grid grid-cols-1 gap-4">
            <?php echo csrf_field(); ?>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Titolo</label>
                    <input type="text" name="title" value="<?php echo e(old('title')); ?>" class="input-field mt-1" required>
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
                    <select name="trigger_type" class="input-field mt-1">
                        <option value="manual" <?php if(old('trigger_type') === 'manual'): echo 'selected'; endif; ?>>Invio manuale</option>
                        <option value="event" <?php if(old('trigger_type') === 'event'): echo 'selected'; endif; ?>>Evento</option>
                        <option value="scheduled" <?php if(old('trigger_type') === 'scheduled'): echo 'selected'; endif; ?>>Programmata</option>
                    </select>
                </div>
                <div x-data="{
                        selected: '<?php echo e(old('event_type')); ?>',
                        options: <?php echo \Illuminate\Support\Js::from($eventOptions)->toHtml() ?>,
                        get current() {
                            return this.options.find(option => option.value === this.selected) || null;
                        }
                    }">
                    <label class="text-xs uppercase font-semibold text-stone-500">Evento collegato</label>
                    <select name="event_type" class="input-field mt-1" x-model="selected">
                        <?php $__currentLoopData = $eventOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option['value']); ?>" <?php if(old('event_type') === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
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
                                <input type="checkbox" name="channels[]" value="<?php echo e($key); ?>" class="rounded text-teal-600 border-stone-300" <?php if(collect(old('channels', ['portal']))->contains($key)): echo 'checked'; endif; ?>>
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
                <textarea name="message_body" rows="4" class="input-field mt-1" placeholder="Testo della notifica"><?php echo e(old('message_body')); ?></textarea>
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

            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-xs uppercase font-semibold text-stone-500">Destinatari</p>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_teachers" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_teachers')): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli insegnanti</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_clients" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_clients')): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli allievi</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_admins" value="1" class="rounded text-teal-600 border-stone-300" <?php if(old('target_all_admins')): echo 'checked'; endif; ?> <?php if(!$canBroadcastAll): echo 'disabled'; endif; ?>>
                        <span>Tutti gli amministratori</span>
                    </label>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Corsi</label>
                    <select name="course_ids[]" multiple class="input-field mt-1 h-32">
                        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>" <?php if(collect(old('course_ids'))->contains($course->id)): echo 'selected'; endif; ?>><?php echo e($course->title); ?></option>
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
                <button type="submit" class="btn-primary">Salva notifica</button>
            </div>
        </form>
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

    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-stone-900">Ultimi invii</h3>
            <?php if($canBroadcastAll): ?>
                <form method="POST" action="<?php echo e(route('notifications.jobs.purge')); ?>" onsubmit="return confirm('Svuotare il log degli invii?');" class="inline-flex">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-[11px] font-semibold text-stone-600 hover:bg-stone-100">Svuota log</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-stone-200">
                <thead class="bg-stone-100 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Notifica</th>
                        <th class="px-3 py-2">Stato</th>
                        <th class="px-3 py-2">Destinatari</th>
                        <th class="px-3 py-2">Inviate</th>
                        <th class="px-3 py-2">Errore</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-semibold text-stone-800"><?php echo e($job->notification->title ?? '—'); ?></p>
                                <p class="text-xs text-stone-500"><?php echo e(optional($job->completed_at) ? $job->completed_at->format('d/m/Y H:i') : 'In coda'); ?></p>
                            </td>
                            <td class="px-3 py-2 text-xs text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 <?php echo e($job->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($job->status === 'failed' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600')); ?>"><?php echo e(ucfirst($job->status)); ?></span>
                            </td>
                            <td class="px-3 py-2 text-center text-xs"><?php echo e($job->target_count); ?></td>
                            <td class="px-3 py-2 text-center text-xs"><?php echo e($job->sent_count); ?></td>
                            <td class="px-3 py-2 text-xs text-rose-500"><?php echo e($job->error_message); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-stone-500 text-sm">Nessun invio registrato.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/admin/notifications/index.blade.php ENDPATH**/ ?>