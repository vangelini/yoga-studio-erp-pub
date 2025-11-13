<?php $__env->startSection('content'); ?>
 

    <?php if(in_array(auth()->user()->role, ['Admin', 'Teacher'])): ?>
        <?php echo $__env->make('dashboard.partials.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('dashboard.partials.client', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/dashboard/index.blade.php ENDPATH**/ ?>