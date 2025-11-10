<?php $__env->startSection('body'); ?>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-100 py-16 px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10 items-stretch">
            <div class="lg:col-span-2 bg-white/70 backdrop-blur-sm border border-teal-100 shadow-xl rounded-3xl p-8 flex flex-col justify-between gap-6">
                <div class="space-y-5">
                    <img src="<?php echo e(asset('images/yoga-logo-big.jpg')); ?>" alt="Shanti Sadhana Logo" class="w-24 h-24 rounded-full shadow-md border border-white/70">
                    <h1 class="text-3xl font-semibold text-stone-900 leading-tight">Bentornato al centro Shanti Sadhana</h1>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Accedi per gestire prenotazioni, iscrizioni e consultare il tuo calendario personale.
                    </p>
                </div>
                <div class="bg-gradient-to-r from-teal-100 to-emerald-100 text-teal-800 px-5 py-4 rounded-2xl border border-teal-200 shadow-inner">
                    <p class="text-sm font-semibold">Non hai un account?</p>
                    <p class="text-sm text-teal-700 mt-1">
                        <a href="<?php echo e(route('register')); ?>" class="underline decoration-teal-600 decoration-2 hover:text-teal-900">
                            Registrati come nuovo cliente.
                        </a>
                    </p>
                </div>
            </div>

            <div class="lg:col-span-3 bg-white shadow-xl border border-stone-200/80 rounded-3xl p-8">
                <h2 class="text-2xl font-semibold text-stone-900 mb-6">Accedi al tuo account</h2>
                <form method="POST" action="<?php echo e(route('login.attempt')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="text-sm font-medium text-stone-600">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            required
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="you@example.com"
                        >
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-stone-600">Password</label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-stone-600">
                            <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                            Ricordami
                        </label>
                        <a href="#" class="text-teal-600 hover:text-teal-800">Password dimenticata?</a>
                    </div>

                    <?php if(config('services.recaptcha.site_key')): ?>
                        <div class="space-y-2">
                            <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                            <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-semibold py-3 rounded-lg hover:from-teal-700 hover:to-emerald-700 transition-colors shadow-md">
                        Accedi
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/auth/login.blade.php ENDPATH**/ ?>