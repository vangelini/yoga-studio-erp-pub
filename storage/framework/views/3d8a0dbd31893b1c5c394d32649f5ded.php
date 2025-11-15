<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="card p-6 space-y-4">
        <h2 class="text-2xl font-semibold text-stone-900">Invio WhatsApp manuale</h2>
        <p class="text-sm text-stone-500">Sono stati generati <?php echo e(count($links)); ?> link WhatsApp per la notifica <strong><?php echo e($notification->title); ?></strong>. Cliccando in "Apri Chat" aprira una TAB del Whatsapp Web, nella nuova TAB inviare il messaggio preconfigurato.</p>



        <div id="whatsapp-links" class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex flex-col gap-1 rounded-xl border border-stone-200 bg-white px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-teal-700"><?php echo e($link['user']->name); ?></span>
                        <span class="text-xs text-stone-400"><?php echo e($link['user']->telephone); ?></span>
                    </div>
                    <p class="text-sm text-stone-600"><?php echo e($link['message']); ?></p>
                    <a href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-800">
                        Apri chat
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-stone-500">Nessun destinatario con numero di telefono valido.</p>
            <?php endif; ?>
        </div>

        <a href="<?php echo e(route('notifications.index')); ?>" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Torna al centro notifiche</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if(count($links)): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urls = <?php echo json_encode(array_column($links, 'url'), 512) ?>;
        let triggered = false;
        const openLinks = () => {
            urls.forEach(url => window.open(url, '_blank'));
        };

        const trigger = document.getElementById('open-all-whatsapp');
        if (trigger) {
            trigger.addEventListener('click', () => {
                if (!triggered) {
                    triggered = true;
                    trigger.classList.add('opacity-50');
                    trigger.setAttribute('disabled', 'disabled');
                }
                openLinks();
            });
        }
    });
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/admin/notifications/whatsapp-links.blade.php ENDPATH**/ ?>