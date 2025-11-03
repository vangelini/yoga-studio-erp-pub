<?php $__env->startSection('content'); ?>
<?php
    $autoGenerateOld = old('membership_auto_generate', $membership_auto_generate);
?>
<div class="max-w-3xl mx-auto" x-data="{ mode: '<?php echo e($receipt_user_password_mode); ?>' }">
    <div class="card p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Impostazioni amministratore</h1>
            <p class="text-sm text-stone-500">Configura le impostazioni generali del centro.</p>
        </div>

        <?php if(session('status')): ?>
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-700">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Quota annuale (Euro)</label>
                <input type="number" step="0.01" name="membership_fee" value="<?php echo e(old('membership_fee', $membership_fee)); ?>" required class="input-field mt-1">
                <?php $__errorArgs = ['membership_fee'];
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

            <div class="flex items-center gap-2">
                <input type="checkbox" name="membership_auto_generate" value="1" id="auto-generate" <?php echo e($autoGenerateOld ? 'checked' : ''); ?> class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                <label for="auto-generate" class="text-sm text-stone-600">Genera automaticamente le pendenze delle quote quando si accede al pannello admin</label>
            </div>

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Morosità quote per pagina</label>
                <input type="number" min="1" max="50" name="membership_morosita_page_size" value="<?php echo e(old('membership_morosita_page_size', $membership_morosita_page_size)); ?>" required class="input-field mt-1">
                <p class="text-xs text-stone-500">Numero di elementi mostrati per pagina nel pannello “Morosità quota associativa”.</p>
                <?php $__errorArgs = ['membership_morosita_page_size'];
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

            <div class="space-y-3">
                <label class="text-xs uppercase font-semibold text-stone-500">Password proprietario ricevute</label>
                <input type="text" name="receipt_owner_password" value="<?php echo e(old('receipt_owner_password', $receipt_owner_password)); ?>" class="input-field" placeholder="Lascia vuoto per nessuna protezione" autocomplete="off">
                <p class="text-xs text-stone-500">Protegge la ricevuta da modifiche non autorizzate. Lasciala vuota per disabilitare la protezione.</p>
                <?php $__errorArgs = ['receipt_owner_password'];
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

            <div class="space-y-3">
                <label class="text-xs uppercase font-semibold text-stone-500">Modalità password destinatario</label>
                <div class="space-y-2">
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="blank" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Nessuna password
                            <span class="block text-xs text-stone-500">Il cliente potrà aprire la ricevuta senza password.</span>
                        </span>
                    </label>
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="email" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Password = email cliente
                            <span class="block text-xs text-stone-500">Usa l'indirizzo email del cliente come password.</span>
                        </span>
                    </label>
                    <label class="inline-flex items-start gap-2 text-sm text-stone-600">
                        <input type="radio" name="receipt_user_password_mode" value="custom" x-model="mode" class="mt-1 text-teal-600 border-stone-300 focus:ring-teal-500">
                        <span>Password personalizzata
                            <span class="block text-xs text-stone-500">Tutte le ricevute useranno la password indicata qui sotto.</span>
                        </span>
                    </label>
                </div>
                <div x-show="mode === 'custom'" x-cloak class="space-y-2">
                    <input type="text" name="receipt_user_password_custom" value="<?php echo e(old('receipt_user_password_custom', $receipt_user_password_custom)); ?>" class="input-field" placeholder="Inserisci la password condivisa" autocomplete="off">
                    <?php $__errorArgs = ['receipt_user_password_custom'];
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
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
                    </svg>
                    Torna al dashboard
                </a>
                <button type="submit" class="btn-primary">Salva impostazioni</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/shanti-sadhana-yoga-center/php-laravel/resources/views/dashboard/settings.blade.php ENDPATH**/ ?>