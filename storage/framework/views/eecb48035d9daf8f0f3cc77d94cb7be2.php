<?php $__env->startSection('body'); ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-teal-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8 bg-white/80 border border-stone-200 rounded-3xl shadow-xl px-8 py-10">
        <div class="text-center space-y-2">
            <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white shadow border border-stone-200 text-teal-600 font-bold text-2xl">
                SS
            </a>
            <h2 class="text-2xl font-bold text-stone-900">Recupera la password</h2>
            <p class="text-sm text-stone-500">Inserisci la tua email e ti invieremo il link per reimpostare la password.</p>
        </div>

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

        <form method="POST" action="<?php echo e(route('password.email')); ?>" class="space-y-5">
            <?php echo csrf_field(); ?>
            <div class="space-y-1">
                <label for="email" class="text-xs uppercase font-semibold text-stone-500">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    required
                    autofocus
                    class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm text-stone-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                    placeholder="you@example.com"
                >
            </div>

            <button type="submit" class="w-full inline-flex justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-teal-700 transition">
                Invia link di reset
            </button>
        </form>

        <div class="text-center text-sm text-stone-500">
            <a href="<?php echo e(route('login')); ?>" class="font-semibold text-teal-600 hover:text-teal-800">Torna al login</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>