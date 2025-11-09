<div class="card p-6 space-y-4">
    <div class="space-y-1">
        <h3 class="text-xl font-semibold text-stone-900">Cambia password</h3>
        <p class="text-sm text-stone-500">Aggiorna la tua password in modo sicuro.</p>
    </div>
    <form method="POST" action="<?php echo e(route('account.password.update')); ?>" class="space-y-3">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div>
            <label class="text-xs uppercase font-semibold text-stone-500">Password attuale</label>
            <input type="password" name="current_password" required class="input-field mt-1" autocomplete="current-password">
            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Nuova password</label>
                <input type="password" name="password" required class="input-field mt-1" autocomplete="new-password">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Conferma nuova password</label>
                <input type="password" name="password_confirmation" required class="input-field mt-1" autocomplete="new-password">
            </div>
        </div>
        <div class="flex justify-end items-center gap-3">
            <a
                href="<?php echo e(route('dashboard')); ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
            >
                Torna alla home
            </a>
            <button type="submit" class="btn-primary text-xs">Salva nuova password</button>
        </div>
    </form>
    <?php if(session('status') && request()->is('account/password')): ?>
        <div class="rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 px-3 py-2 text-sm"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/partials/account-password.blade.php ENDPATH**/ ?>