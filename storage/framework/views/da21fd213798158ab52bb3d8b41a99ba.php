<?php $__env->startSection('content'); ?>
    <div class="mb-10">
        <h2 class="text-3xl font-light text-stone-700">
            Welcome,
            <span class="font-semibold text-teal-700"><?php echo e(Str::before(auth()->user()->name, ' ')); ?></span>
        </h2>
        <p class="text-stone-500 mt-2">You are logged in as a <span class="font-medium"><?php echo e(auth()->user()->role); ?></span>.</p>
    </div>

    <?php if(auth()->user()->role === 'Admin'): ?>
        <?php echo $__env->make('dashboard.partials.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php elseif(auth()->user()->role === 'Teacher'): ?>
        <?php echo $__env->make('dashboard.partials.teacher', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('dashboard.partials.client', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/shanti-sadhana-yoga-center/php-laravel/resources/views/dashboard/index.blade.php ENDPATH**/ ?>