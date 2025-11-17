<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-4">
    <div class="card p-6 space-y-3">
        <h1 class="text-2xl font-semibold text-stone-900">Pagamento tramite bonifico</h1>
        <p class="text-sm text-stone-500">Segui le istruzioni riportate qui sotto per completare il bonifico.</p>
        <div class="prose prose-stone max-w-none text-stone-700">
            <?php echo nl2br(e($message)); ?>

        </div>
    </div>
    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
        Torna al dashboard
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\yoga-studio-erp\resources\views/dashboard/bank-transfer.blade.php ENDPATH**/ ?>