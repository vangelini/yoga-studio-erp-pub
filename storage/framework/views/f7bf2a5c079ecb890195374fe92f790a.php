<?php $__env->startSection('content'); ?>
    <div class="mb-10">
        <h2 class="text-3xl font-light text-stone-700">
            Benvenuta/o,
            <span class="font-semibold text-teal-700"><?php echo e(Str::before(auth()->user()->name, ' ')); ?></span>
        </h2>
        
    </div>

    <?php if(auth()->user()->role === 'Admin'): ?>
        <?php echo $__env->make('dashboard.partials.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php elseif(auth()->user()->role === 'Teacher'): ?>
        <?php echo $__env->make('dashboard.partials.teacher', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('dashboard.partials.client', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/index.blade.php ENDPATH**/ ?>