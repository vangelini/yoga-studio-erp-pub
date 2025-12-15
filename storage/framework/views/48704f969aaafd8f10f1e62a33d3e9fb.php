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
    $flashStatus = session('status');
    $flashErrors = $errors->any() ? $errors->all() : [];
?>
<style>
    .min-h-screen > .mx-auto{
 padding-top:0px;
}
</style>
<section
    x-data="{
        showCreateClient: false,
        expandedClient: <?php echo \Illuminate\Support\Js::from($initialExpandedClient)->toHtml() ?>,
        flashOpen: <?php echo \Illuminate\Support\Js::from((bool) $flashStatus || !empty($flashErrors))->toHtml() ?>,
        flashStatus: <?php echo \Illuminate\Support\Js::from($flashStatus)->toHtml() ?>,
        flashErrors: <?php echo \Illuminate\Support\Js::from($flashErrors)->toHtml() ?>,
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
    >
    <template x-if="flashOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-stone-200 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-stone-900">Notifica</h3>
                <div class="space-y-2 text-sm text-stone-700 max-h-64 overflow-y-auto">
                    <template x-if="flashStatus">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700" x-text="flashStatus"></div>
                    </template>
                    <template x-if="flashErrors.length">
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">
                            <ul class="list-disc list-inside space-y-1">
                                <template x-for="(err, idx) in flashErrors" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" @click="flashOpen = false">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </template>
  
    <?php echo $__env->make('admin.clients.partials.management', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:border-stone-300 hover:text-stone-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Torna al dashboard
                </a>
</section>
<?php $__env->stopSection(); ?>
              

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\yoga-studio-erp\resources\views/admin/clients/index.blade.php ENDPATH**/ ?>