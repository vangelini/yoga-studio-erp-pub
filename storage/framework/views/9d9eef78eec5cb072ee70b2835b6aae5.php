<?php $__env->startSection('body'); ?>
    <div class="flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-3 group transition">
                    <img src="<?php echo e(asset('images/yoga-logo.jpg')); ?>" alt="Shanti Sadhana Logo" class="w-10 h-10 rounded-full ring-2 ring-transparent group-hover:ring-teal-200 transition">
                    <div>
                        <h1 class="text-2xl font-bold text-teal-800 group-hover:text-teal-700 transition">Shanti Sadhana</h1>
                        <p class="text-sm text-stone-500 -mt-1">Find your inner peace.</p>
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-stone-500">Benvenuta/o</p>
                        <p class="font-semibold text-teal-700"><?php echo e(auth()->user()->name); ?></p>
                    </div>
                    <a
                        href="<?php echo e(route('account.password.edit')); ?>"
                        class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104-.896-2-2-2m8 10V9a4 4 0 00-4-4H9a4 4 0 00-4 4v10" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 15h10" />
                        </svg>
                        Cambia password
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="bg-stone-200 text-stone-700 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 container mx-auto px-4 py-8">
           

            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <footer class="bg-white border-t border-stone-200 py-6">
            <div class="container mx-auto px-4 text-center text-stone-500 text-sm">
                &copy; <?php echo e(now()->year); ?> Shanti Sadhana Yoga Center. All Rights Reserved.
            </div>
        </footer>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/layouts/app.blade.php ENDPATH**/ ?>