<?php $__env->startSection('content'); ?>
<?php
    $phonePrefixes = [
        ['code' => '+39', 'name' => 'Italia'],
        ['code' => '+33', 'name' => 'Francia'],
        ['code' => '+49', 'name' => 'Germania'],
        ['code' => '+34', 'name' => 'Spagna'],
        ['code' => '+44', 'name' => 'Regno Unito'],
        ['code' => '+1', 'name' => 'Stati Uniti'],
    ];
?>

<section
    x-data="{
        showCreateTeacher: false,
        expandedTeacher: null,
        toggleTeacher(id) {
            this.expandedTeacher = this.expandedTeacher === id ? null : id;
        }
    }"
    class="space-y-10"
>
    <?php if(session('status')): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc list-inside space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-green-800">Insegnanti del centro</h3>
                <p class="text-sm text-stone-500">Gestisci anagrafica degli insegnanti.</p>
            </div>
            <button type="button" class="btn-primary text-xs self-start md:self-auto" @click="showCreateTeacher = !showCreateTeacher">
                <span class="text-sm font-semibold" x-text="showCreateTeacher ? 'Nascondi ' : 'Nuovo Insegnante'"></span>
            </button>
        </div>

        <?php if (! ($private_lessons_enabled)): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                La gestione delle lezioni individuali è disattivata dalle impostazioni generali. I clienti e gli insegnanti non vedranno le relative sezioni.
            </div>
        <?php endif; ?>

        <form
            x-show="showCreateTeacher"
            x-transition
            method="POST"
            action="<?php echo e(route('admin.users.store')); ?>"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6 mb-4"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="role" value="Teacher">
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                <input type="text" name="first_name" required class="input-field text-sm" placeholder="Nome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" required class="input-field text-sm" placeholder="Cognome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" required class="input-field text-sm" placeholder="insegnante@example.com">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Password temporanea</label>
                <input type="password" name="password" minlength="6" required class="input-field text-sm" placeholder="••••••">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                <select name="telephone_country" class="input-field text-sm">
                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['code']); ?>" <?php if($option['code'] === '+39'): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                <input type="text" name="telephone" required class="input-field text-sm" placeholder="000 000 0000">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                <input type="text" name="residenza_citta" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                <input type="text" name="residenza_provincia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                <input type="text" name="residenza_stato" value="Italia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                <input type="text" name="residenza_via" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                <input type="text" name="residenza_numero_civico" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                <input type="text" name="codice_fiscale" required class="input-field text-sm uppercase">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2 space-y-2">
                <p class="text-xs uppercase text-stone-500 font-semibold">Permessi dashboard docente</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                        <input type="hidden" name="can_manage_courses" value="0">
                        <input type="checkbox" name="can_manage_courses" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                        <span>Gestione corsi assegnati</span>
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                        <input type="hidden" name="can_manage_payments" value="0">
                        <input type="checkbox" name="can_manage_payments" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                        <span>Gestione pagamenti</span>
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                        <input type="hidden" name="can_manage_students" value="0">
                        <input type="checkbox" name="can_manage_students" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                        <span>Gestione allievi</span>
                    </label>
                </div>
            </div>
            <?php if($private_lessons_enabled): ?>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input id="teacher-private" type="checkbox" name="can_host_private" value="1" class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                    <label for="teacher-private" class="text-sm text-stone-600">Abilita immediatamente le lezioni private</label>
                </div>
            <?php endif; ?>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Registra insegnante</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Nome</th>
                        <th class="px-4 py-3 text-left font-semibold">Email</th>
                        <th class="px-4 py-3 text-left font-semibold">Telefono</th>
                        <th class="px-4 py-3 text-left font-semibold">Stato</th>
                        <th class="px-4 py-3 text-left font-semibold">Permessi</th>
                        <?php if($private_lessons_enabled): ?>
                            <th class="px-4 py-3 text-left font-semibold">Lezioni private</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-left font-semibold">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php $__empty_1 = true; $__currentLoopData = $teacherAdminList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $teacherUser = $teacher->user;
                            $teacherPhoneParts = explode(' ', $teacherUser->telephone ?? '', 2);
                            $teacherPrefix = $teacherPhoneParts[0] ?? '+39';
                            $teacherNumber = $teacherPhoneParts[1] ?? '';
                            $teacherWhatsapp = preg_replace('/\D+/', '', $teacherUser->telephone ?? '');
                            $teacherCourseTitles = $teacher->courses->pluck('title')->filter()->values();
                        ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800"><?php echo e($teacherUser->name); ?></td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($teacherUser->email): ?>
                                    <a href="mailto:<?php echo e($teacherUser->email); ?>" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted"><?php echo e($teacherUser->email); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($teacherUser->telephone && $teacherWhatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($teacherWhatsapp); ?>" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        <?php echo e($teacherUser->telephone); ?>

                                    </a>
                                <?php else: ?>
                                    <?php echo e($teacherUser->telephone ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($teacherUser->status === 'active'): ?> bg-emerald-100 text-emerald-700
                                    <?php elseif($teacherUser->status === 'pending'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-rose-100 text-rose-700 <?php endif; ?>">
                                    <?php echo e(ucfirst($teacherUser->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                    $permBadges = [
                                        ['label' => 'Corsi', 'enabled' => $teacher->can_manage_courses],
                                        ['label' => 'Pagamenti', 'enabled' => $teacher->can_manage_payments],
                                        ['label' => 'Allievi', 'enabled' => $teacher->can_manage_students],
                                    ];
                                ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $permBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($perm['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500'); ?>">
                                            <?php echo e($perm['label']); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                            <?php if($private_lessons_enabled): ?>
                                <td class="px-4 py-3 text-stone-600">
                                    <?php echo $teacher->can_host_private
                                        ? '<span class="text-emerald-600 font-semibold">Abilitate</span>'
                                        : '<span class="text-stone-500">Disabilitate</span>'; ?>

                                </td>
                            <?php endif; ?>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleTeacher(<?php echo e($teacher->id); ?>)">
                                    <span x-text="expandedTeacher === <?php echo e($teacher->id); ?> ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedTeacher === <?php echo e($teacher->id); ?>" x-cloak x-transition>
                            <td colspan="<?php echo e($private_lessons_enabled ? 7 : 6); ?>" class="px-4 pb-5">
                                <div class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
                                        <form method="POST" action="<?php echo e(route('admin.users.profile', $teacherUser)); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                                                <input type="text" name="first_name" value="<?php echo e($teacherUser->first_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                                                <input type="text" name="last_name" value="<?php echo e($teacherUser->last_name); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                                                <input type="email" name="email" value="<?php echo e($teacherUser->email); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                                                <select name="telephone_country" class="input-field text-sm">
                                                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($option['code']); ?>" <?php if($teacherPrefix === $option['code']): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                                                <input type="text" name="telephone" value="<?php echo e($teacherNumber); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                                                <input type="text" name="residenza_citta" value="<?php echo e($teacherUser->residenza_citta); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                                                <input type="text" name="residenza_provincia" value="<?php echo e($teacherUser->residenza_provincia); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                                                <input type="text" name="residenza_stato" value="<?php echo e($teacherUser->residenza_stato); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                                                <input type="text" name="residenza_via" value="<?php echo e($teacherUser->residenza_via); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                                                <input type="text" name="residenza_numero_civico" value="<?php echo e($teacherUser->residenza_numero_civico); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                                                <input type="text" name="codice_fiscale" value="<?php echo e($teacherUser->codice_fiscale); ?>" required class="input-field text-sm uppercase">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                                                <input type="text" name="luogo_nascita" value="<?php echo e($teacherUser->luogo_nascita); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                                                <input type="date" name="data_nascita" value="<?php echo e(optional($teacherUser->data_nascita)->format('Y-m-d')); ?>" required class="input-field text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-stone-500 font-semibold">Stato utente</label>
                                                <select name="status" class="input-field text-sm">
                                                    <?php $__currentLoopData = ['active' => 'Attivo', 'pending' => 'In attesa', 'disabled' => 'Disabilitato']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value); ?>" <?php if(($teacherUser->status ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <?php if($teacherUser->role === 'Teacher'): ?>
                                                <div class="md:col-span-2 space-y-3">
                                                    <div>
                                                        <p class="text-xs uppercase text-stone-500 font-semibold">Permessi dashboard docente</p>
                                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                            <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                                                <input type="hidden" name="teacher_can_manage_courses" value="0">
                                                                <input type="checkbox" name="teacher_can_manage_courses" value="1" <?php if($teacher->can_manage_courses): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                                <span>Gestione corsi assegnati</span>
                                                            </label>
                                                            <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                                                <input type="hidden" name="teacher_can_manage_payments" value="0">
                                                                <input type="checkbox" name="teacher_can_manage_payments" value="1" <?php if($teacher->can_manage_payments): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                                <span>Gestione pagamenti</span>
                                                            </label>
                                                            <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                                                <input type="hidden" name="teacher_can_manage_students" value="0">
                                                                <input type="checkbox" name="teacher_can_manage_students" value="1" <?php if($teacher->can_manage_students): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                                <span>Gestione allievi</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if($private_lessons_enabled): ?>
                                                        <label class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                                            <input type="hidden" name="teacher_can_host_private" value="0">
                                                            <input type="checkbox" name="teacher_can_host_private" value="1" <?php if($teacher->can_host_private): echo 'checked'; endif; ?> class="h-4 w-4 text-teal-600 border-stone-300 rounded">
                                                            <span>Può tenere lezioni private</span>
                                                        </label>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="md:col-span-2 flex justify-end gap-2">
                                                <button type="reset" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">Cancella</button>
                                                <button type="submit" class="btn-primary text-sm">Salva dati</button>
                                            </div>
                                        </form>
                                        <div class="lg:col-span-4 space-y-2">
                                            <p class="text-xs uppercase text-stone-500 font-semibold">Corsi assegnati</p>
                                            <div class="flex flex-wrap gap-2 text-sm">
                                                <?php if($teacherCourseTitles->isNotEmpty()): ?>
                                                    <?php $__currentLoopData = $teacherCourseTitles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="inline-flex items-center rounded-full bg-teal-50 border border-teal-100 px-3 py-1 text-teal-700 font-semibold"><?php echo e($title); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="text-stone-500">Nessun corso assegnato</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <form method="POST" action="<?php echo e(route('admin.users.passwordEmail', $teacherUser)); ?>" class="flex items-center gap-3">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="text-xs bg-rose-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-rose-600 transition-colors">Invia reset password</button>
                                            </form>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-stone-600">
                                                <span class="font-semibold text-stone-700 uppercase tracking-wide">Verifica Email:</span>
                                                <?php if($teacherUser->email_verified_at): ?>
                                                    <span class="text-emerald-600 font-semibold">Sì (<?php echo e(optional($teacherUser->email_verified_at)->format('d/m/Y H:i')); ?>)</span>
                                                <?php else: ?>
                                                    <span class="text-amber-600 font-semibold">No</span>
                                                    <form method="POST" action="<?php echo e(route('admin.users.resendVerification', $teacherUser)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-xs bg-amber-500 text-white font-semibold px-3 py-2 rounded-lg hover:bg-amber-600 transition-colors">Reinvia email</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($private_lessons_enabled ? 7 : 6); ?>" class="px-4 py-6 text-center text-stone-500">Nessun insegnante registrato al momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\yoga-studio-erp\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>