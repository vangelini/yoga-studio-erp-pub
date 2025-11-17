<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Contabilità</h1>
            <p class="text-sm text-stone-500">Elenco pagamenti con filtri per stato e data di pagamento.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="<?php echo e(route('admin.accounting.export', request()->query())); ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-3 py-2 text-xs font-semibold text-teal-600 hover:bg-teal-50"
            >
                Esporta CSV
            </a>
            <form method="GET" action="<?php echo e(route('admin.accounting.exportReceipts')); ?>" class="flex items-center gap-2">
                <input type="number" name="year" value="<?php echo e(request('year', now()->year)); ?>" min="2000" max="<?php echo e(now()->year + 1); ?>" class="input-field text-xs w-24" title="Anno ricevute">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                    Esporta ricevute ZIP
                </button>
            </form>
            
        </div>
    </div>

    <div x-data="{ open: false }" class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <button
            type="button"
            class="flex w-full items-center justify-between px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50"
            @click="open = !open"
        >
            <span>Filtri contabilit&aacute;</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6" />
            </svg>
        </button>
        <form
            x-show="open"
            x-cloak
            x-transition
            method="GET"
            action="<?php echo e(route('admin.accounting.index')); ?>"
            class="flex flex-col gap-3 px-4 py-3 text-sm md:flex-row md:items-end"
        >
            <div class="min-w-[180px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Stato</label>
                <select name="status" class="input-field text-sm">
                    <option value="">Tutti</option>
                    <?php $__currentLoopData = ['pending' => 'In attesa', 'paid' => 'Pagato', 'waived' => 'Annullato']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('status') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Da data pagamento</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="input-field text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">A data pagamento</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="input-field text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Ordina per</label>
                <select name="sort" class="input-field text-sm">
                    <?php $__currentLoopData = ['paid_at' => 'Data pagamento', 'due_date' => 'Data scadenza', 'amount' => 'Importo', 'status' => 'Stato', 'type' => 'Tipo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('sort', 'paid_at') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Direzione</label>
                <select name="dir" class="input-field text-sm">
                    <option value="asc" <?php if(request('dir') === 'asc'): echo 'selected'; endif; ?>>Ascendente</option>
                    <option value="desc" <?php if(request('dir', 'desc') === 'desc'): echo 'selected'; endif; ?>>Discendente</option>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="text-xs uppercase font-semibold text-stone-500">Elementi per pagina</label>
                <select name="per_page" class="input-field text-sm">
                    <?php $__currentLoopData = [25, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option); ?>" <?php if(request('per_page', $perPage ?? 25) == $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-center gap-2 md:ml-auto">
                <button type="submit" class="btn-primary text-xs">Filtra</button>
                <a href="<?php echo e(route('admin.accounting.index')); ?>" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Pulisci</a>
                
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Allievo</th>
                    <th class="px-3 py-2 text-left">Importo</th>
                    <th class="px-3 py-2 text-left">Tipo</th>
                    <th class="px-3 py-2 text-left">Metodo</th>
                    <th class="px-3 py-2 text-left">CRO / Riferimento</th>
                    <th class="px-3 py-2 text-left">Nota pagamento</th>
                    <th class="px-3 py-2 text-left">Stato</th>
                    <th class="px-3 py-2 text-left">Data pagamento</th>
                    <th class="px-3 py-2 text-left">Scadenza</th>
                    <th class="px-3 py-2 text-left">Anno</th>
                    <th class="px-3 py-2 text-left">Operatore</th>
                    <th class="px-3 py-2 text-left">Ricevuta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-stone-50">
                        <td class="px-3 py-2"><?php echo e($payment->id); ?></td>
                        <td class="px-3 py-2">
                            <div class="flex flex-col">
                                <span class="font-semibold text-stone-800"><?php echo e($payment->user?->name ?? '—'); ?></span>
                                <span class="text-xs text-stone-500"><?php echo e($payment->user?->email); ?></span>
                            </div>
                        </td>
                        <td class="px-3 py-2 font-semibold text-stone-800">€ <?php echo e(number_format($payment->amount ?? 0, 2, ',', '.')); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e($payment->type); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e($payment->method ?? '—'); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e($payment->meta['transfer_reference'] ?? '—'); ?></td>
                        <td class="px-3 py-2 text-stone-600">
                            <?php echo e($payment->meta['manual_note'] ?? $payment->status_reason ?? '—'); ?>

                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold
                                <?php if($payment->status === 'paid'): ?> bg-emerald-100 text-emerald-700
                                <?php elseif($payment->status === 'pending'): ?> bg-amber-100 text-amber-700
                                <?php else: ?> bg-rose-100 text-rose-700 <?php endif; ?>">
                                <?php echo e($payment->status); ?>

                            </span>
                        </td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e(optional($payment->paid_at)->format('d/m/Y') ?: '—'); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e(optional($payment->due_date)->format('d/m/Y') ?: '—'); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e($payment->receipt_year ?? '—'); ?></td>
                        <td class="px-3 py-2 text-stone-600"><?php echo e($payment->processedBy?->name ?? '—'); ?></td>
                        <td class="px-3 py-2">
                            <?php if($payment->receipt_path): ?>
                                <a href="<?php echo e(route('payments.receipt', $payment->id)); ?>" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted text-xs">
                                    Apri ricevuta
                                </a>
                            <?php else: ?>
                                <span class="text-[11px] text-stone-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="px-3 py-4 text-center text-stone-500">Nessun pagamento trovato.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-xs text-stone-500">
            <form method="GET" action="<?php echo e(route('admin.accounting.index')); ?>" class="inline">
                Mostra
                <?php $__currentLoopData = request()->except('page', 'per_page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <select name="per_page" class="input-field text-xs inline-block w-auto align-middle" onchange="this.form.submit()">
                    <?php $__currentLoopData = [25, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option); ?>" <?php if($payments->perPage() == $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select> righe per pagina.
                </form>
            </div>
        <div>
            <?php echo e($payments->links()); ?>

        </div>
    </div>
    <div class="mt-2">
        <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Torna al dashboard
        </a>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\yoga-studio-erp\resources\views/admin/accounting/index.blade.php ENDPATH**/ ?>