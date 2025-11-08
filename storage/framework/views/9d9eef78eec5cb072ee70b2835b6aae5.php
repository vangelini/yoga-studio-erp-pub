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
            <?php if(session('status')): ?>
                <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 px-4 py-3">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

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