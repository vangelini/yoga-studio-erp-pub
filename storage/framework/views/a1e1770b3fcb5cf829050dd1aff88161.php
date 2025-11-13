<?php $__env->startSection('content'); ?>
<?php
    $clientPagePermissions = $clientPagePermissions ?? [
        'mode' => 'admin',
        'can_create' => true,
        'can_export' => true,
        'can_manage_account' => true,
        'can_manage_profile' => true,
        'can_manage_documents' => true,
        'can_manage_payments' => true,
    ];
?>
<section
    x-data="{
        showCreateClient: false,
        expandedClient: <?php echo \Illuminate\Support\Js::from($initialExpandedClient)->toHtml() ?>,
        toggleClient(id) {
            this.expandedClient = this.expandedClient === id ? null : id;
        },
        setInitialClient() {
            const url = new URL(window.location.href);
            const queryClient = Number(url.searchParams.get('client_id'));
            if (queryClient) {
                this.expandedClient = queryClient;
            } else if (!this.expandedClient && url.hash.startsWith('#client-')) {
                const fromHash = Number(url.hash.replace('#client-', ''));
                if (fromHash) {
                    this.expandedClient = fromHash;
                }
            }
            this.scrollToSelected();
        },
        scrollToSelected() {
            if (!this.expandedClient) {
                return;
            }
            this.$nextTick(() => {
                const target = document.getElementById('client-' + this.expandedClient);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
    }"
    x-init="setInitialClient()"
    class="space-y-10"
>
   
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
  
    <?php echo $__env->make('admin.clients.partials.management', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:border-stone-300 hover:text-stone-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Torna al dashboard
                </a>
</section>
<?php $__env->stopSection(); ?>
              

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/admin/clients/index.blade.php ENDPATH**/ ?>