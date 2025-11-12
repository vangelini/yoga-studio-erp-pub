
    <div class="card p-6 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-stone-900">Allieve/i</h3>
                <p class="text-sm text-stone-500">Gestisci dati anagrafici, stato account e pagamenti.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="<?php echo e(route('admin.users.export')); ?>"
                    class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-white px-4 py-2 text-xs font-semibold text-teal-600 transition-colors hover:border-teal-300 hover:bg-teal-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                    </svg>
                    Scarica elenco (.csv)
                </a>
                <button type="button" class="btn-primary text-xs self-start md:self-auto" @click="showCreateClient = !showCreateClient">
                    <span class="text-sm font-semibold" x-text="showCreateClient ? 'Nascondi form' : 'Nuova allieva/o'"></span>
                </button>
            </div>
        </div>

        <form
            x-show="showCreateClient"
            x-transition
            method="POST"
            action="<?php echo e(route('admin.users.store')); ?>"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-stone-200 rounded-2xl bg-stone-50 px-5 py-6"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="role" value="Client">
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                <input type="text" name="first_name" required class="input-field text-sm" placeholder="Nome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" required class="input-field text-sm" placeholder="Cognome">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" required class="input-field text-sm" placeholder="you@example.com">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Password temporanea</label>
                <input type="password" name="password" minlength="6" required class="input-field text-sm" placeholder="••••••">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                <select name="telephone_country" class="input-field text-sm">
                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['code']); ?>" <?php if($option['code'] === '+39'): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                <input type="text" name="telephone" required class="input-field text-sm" placeholder="000 000 0000">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                <input type="text" name="residenza_citta" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                <input type="text" name="residenza_provincia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                <input type="text" name="residenza_stato" value="Italia" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                <input type="text" name="residenza_via" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                <input type="text" name="residenza_numero_civico" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                <input type="text" name="codice_fiscale" required maxlength="16" pattern="[A-Za-z0-9]{16}" oninput="this.value = this.value.toUpperCase()" class="input-field text-sm" placeholder="CODICEFISCALE16">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary text-sm">Registra allieva/o</button>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-100 text-stone-600 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Nome</th>
                        <th class="px-4 py-3 text-left font-semibold">Email</th>
                        <th class="px-4 py-3 text-left font-semibold">Telefono</th>
                        <th class="px-4 py-3 text-left font-semibold">Stato Account</th>
                        <th class="px-4 py-3 text-left font-semibold">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $phoneParts = explode(' ', $client->telephone ?? '', 2);
                            $clientPrefix = $phoneParts[0] ?? '+39';
                            $clientNumber = $phoneParts[1] ?? '';
                            $clientWhatsapp = preg_replace('/\D+/', '', $client->telephone ?? '');

                            $documentCollection = collect($client->getAttribute('admin_documents') ?? []);
                            $documentsByType = $documentCollection->keyBy('type');
                            $missingDocuments = collect(array_keys($documentDefinitions))
                                ->reject(fn ($type) => $documentsByType->has($type))
                                ->values();
                            $pendingPaymentsCount = (int) ($client->getAttribute('admin_pending_payments_count') ?? 0);
                        ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-800"><?php echo e($client->name); ?></td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($client->email): ?>
                                    <a href="mailto:<?php echo e($client->email); ?>" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted"><?php echo e($client->email); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <?php if($client->telephone && $clientWhatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($clientWhatsapp); ?>" target="_blank" rel="noopener" class="text-teal-600 hover:text-teal-800 font-semibold underline decoration-dotted">
                                        <?php echo e($client->telephone); ?>

                                    </a>
                                <?php else: ?>
                                    <?php echo e($client->telephone ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($client->status === 'active'): ?> bg-emerald-100 text-emerald-700
                                    <?php elseif($client->status === 'pending'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-rose-100 text-rose-700 <?php endif; ?>">
                                    <?php echo e(ucfirst($client->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="text-xs font-semibold inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-3 py-1.5 rounded-lg hover:bg-stone-300 transition-colors" @click="toggleClient(<?php echo e($client->id); ?>)">
                                    <span x-text="expandedClient === <?php echo e($client->id); ?> ? 'Nascondi' : 'Gestisci'"></span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="expandedClient === <?php echo e($client->id); ?>" x-cloak x-transition>
                            <td colspan="5" class="px-4 pb-5">
                                <div x-data="{ showProfile: false }" class="bg-stone-50 border border-stone-200 rounded-lg p-5 space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-600">Dettagli allieva/o</h4>
            
            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                <?php if($missingDocuments->isEmpty()): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Documenti completi</span>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l5.451 9.698c.75 1.335-.213 3.003-1.742 3.003H4.548c-1.53 0-2.492-1.668-1.743-3.003l5.452-9.698zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd" />
                        </svg>
                        <span><?php echo e($missingDocuments->count()); ?> documenti mancanti</span>
                    </span>
                <?php endif; ?>

                <?php if($pendingPaymentsCount > 0): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-1 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.75a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0v-4.5zM10 13a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                        </svg>
                        <span><?php echo e($pendingPaymentsCount); ?> pagamenti da gestire</span>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Pagamenti regolari</span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-100"
            @click="showProfile = !showProfile"
            x-text="showProfile ? 'Nascondi dati' : 'Mostra dati anagrafici'"
        ></button>
    </div>

    <form method="POST" action="<?php echo e(route('admin.users.profile', $client)); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div x-show="showProfile" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Nome</label>
                <input type="text" name="first_name" value="<?php echo e($client->first_name); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Cognome</label>
                <input type="text" name="last_name" value="<?php echo e($client->last_name); ?>" required class="input-field text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase text-stone-500 font-semibold">Email</label>
                <input type="email" name="email" value="<?php echo e($client->email); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Prefisso</label>
                <select name="telephone_country" class="input-field text-sm">
                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['code']); ?>" <?php if($clientPrefix === $option['code']): echo 'selected'; endif; ?>><?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero</label>
                <input type="text" name="telephone" value="<?php echo e($clientNumber); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Città</label>
                <input type="text" name="residenza_citta" value="<?php echo e($client->residenza_citta); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Provincia</label>
                <input type="text" name="residenza_provincia" value="<?php echo e($client->residenza_provincia); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Stato</label>
                <input type="text" name="residenza_stato" value="<?php echo e($client->residenza_stato); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Via</label>
                <input type="text" name="residenza_via" value="<?php echo e($client->residenza_via); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Numero civico</label>
                <input type="text" name="residenza_numero_civico" value="<?php echo e($client->residenza_numero_civico); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Codice fiscale</label>
                <input type="text" name="codice_fiscale" value="<?php echo e($client->codice_fiscale); ?>" required class="input-field text-sm uppercase">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Luogo di nascita</label>
                <input type="text" name="luogo_nascita" value="<?php echo e($client->luogo_nascita); ?>" required class="input-field text-sm">
            </div>
            <div>
                <label class="text-xs uppercase text-stone-500 font-semibold">Data di nascita</label>
                <input type="date" name="data_nascita" value="<?php echo e(optional($client->data_nascita)->format('Y-m-d')); ?>" required class="input-field text-sm">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <label for="status-<?php echo e($client->id); ?>" class="text-xs uppercase text-stone-500 font-semibold">Stato account</label>
                <select id="status-<?php echo e($client->id); ?>" name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                    <?php $__currentLoopData = ['active', 'pending', 'disabled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($statusOption); ?>" <?php if($client->status === $statusOption): echo 'selected'; endif; ?>><?php echo e(ucfirst($statusOption)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 md:justify-end">
                <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Verifica Email:</span>
                <?php if($client->email_verified_at): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-600 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span><?php echo e(optional($client->email_verified_at)->format('d/m/Y H:i')); ?></span>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-1 text-rose-600 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span>Non verificata</span>
                    </span>
                    <form method="POST" action="<?php echo e(route('admin.users.resendVerification', $client)); ?>" class="inline-flex"
                        onsubmit="return confirm('Inviare una nuova email di verifica a <?php echo e($client->email); ?>?');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-amber-600 transition-colors">
                            Reinvia verifica
                        </button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('admin.users.passwordEmail', $client)); ?>" class="inline-flex"
                    onsubmit="return confirm('Vuoi inviare un\'email di reset password a <?php echo e($client->email); ?>? L\'utente riceverà un link per impostare una nuova password.');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-rose-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-rose-600 transition-colors">
                        Invia reset password
                    </button>
                </form>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700 transition"
                    onclick="return confirm('Salvare le modifiche per <?php echo e($client->name); ?>? Verranno aggiornati i dati del profilo.');">
                    Salva dati
                </button>
            </div>

        </div>

        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            

            <div
                x-show="showProfile"
                x-cloak
                x-data="{
                    definitions: <?php echo e(\Illuminate\Support\Js::from($documentDefinitions)); ?>,
                    docs: <?php echo e(\Illuminate\Support\Js::from($documentCollection->values())); ?>,
                    uploadUrl: '<?php echo e(route('admin.users.documents.store', $client, false)); ?>',
                    csrf: '<?php echo e(csrf_token()); ?>',
                    message: null,
                    messageType: 'success',
                    _timeout: null,
                    docByType(type) {
                        return this.docs.find(doc => doc.type === type) || null;
                    },
                    entries() {
                        return Object.entries(this.definitions).map(([type, label]) => {
                            const doc = this.docByType(type);
                            return {
                                type,
                                label,
                                present: !!doc,
                                url: doc ? (doc.download_url || doc.downloadUrl || doc.url) : null,
                                uploaded_at_display: doc ? (doc.uploaded_at_display ?? doc.uploadedAtDisplay ?? '') : null,
                                original_name: doc ? (doc.original_name ?? doc.originalName ?? '') : null,
                            };
                        });
                    },
                    setMessage(text, type = 'success') {
                        this.message = text;
                        this.messageType = type;
                        clearTimeout(this._timeout);
                        this._timeout = setTimeout(() => {
                            this.message = null;
                        }, 3000);
                    },
                    async handleFile(event, type) {
                        const file = event.target.files[0];
                        if (!file) {
                            return;
                        }
                        const formData = new FormData();
                        formData.append('document_type', type);
                        formData.append('document_file', file);
                        formData.append('_token', this.csrf);
                        try {
                            const response = await fetch(this.uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                                credentials: 'same-origin',
                            });
                            const raw = await response.text();
                            let data = {};
                            try {
                                data = raw ? JSON.parse(raw) : {};
                            } catch (parseError) {
                                data = { message: raw?.trim() ?? null };
                            }
                            if (!response.ok || !data.document) {
                                throw new Error(data.message || response.statusText || 'Caricamento non riuscito');
                            }
                            const doc = Object.assign({}, data.document, {
                                uploaded_at_display: data.document.uploaded_at_display ?? data.document.uploadedAtDisplay ?? '',
                                original_name: data.document.original_name ?? data.document.originalName ?? '',
                                download_url: data.document.download_url ?? data.document.downloadUrl ?? data.document.url ?? null,
                            });
                            const index = this.docs.findIndex(existing => existing.type === type);
                            if (index >= 0) {
                                this.docs.splice(index, 1, doc);
                            } else {
                                this.docs.push(doc);
                            }
                            this.setMessage(data.message || 'Documento aggiornato correttamente', 'success');
                        } catch (error) {
                            this.setMessage(error.message || 'Errore durante il caricamento', 'error');
                        } finally {
                            event.target.value = '';
                        }
                    },
                }"
                class="border border-dashed border-stone-300 rounded-xl bg-white/60 px-3 py-2"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Documenti personali</p>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <template x-for="entry in entries()" :key="entry.type">
                        <div class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white/80 px-3 py-1 shadow-sm">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full"
                                :class="entry.present ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'">
                                <template x-if="entry.present">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414l2.543 2.543 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template x-if="!entry.present">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l5.451 9.698c.75 1.335-.213 3.003-1.742 3.003H4.548c-1.53 0-2.492-1.668-1.743-3.003l5.452-9.698zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                            </span>
                            <span class="font-semibold text-stone-700" x-text="entry.label"></span>
                            <span class="text-[10px] text-stone-400" x-text="entry.present ? (entry.uploaded_at_display || entry.original_name || 'Aggiornato') : 'Manca documento'"></span>
                            <div class="flex items-center gap-1">
                                <template x-if="entry.present && entry.url">
                                    <a :href="entry.url" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-md border border-teal-200 px-2 py-0.5 text-[10px] font-semibold text-teal-600 hover:bg-teal-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                        </svg>
                                        Scarica
                                    </a>
                                </template>
                                <label class="inline-flex items-center gap-1 rounded-md border border-stone-300 px-2 py-0.5 text-[10px] font-semibold text-stone-600 hover:bg-stone-100 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l3.5-3.5M12 16l-3.5-3.5" />
                                    </svg>
                                    Carica
                                    <input type="file" class="sr-only" accept=".pdf,image/*" @change="handleFile($event, entry.type)">
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
                <div
                    x-show="message"
                    x-transition
                    class="fixed top-4 right-4 z-50 rounded-xl border px-4 py-2 text-xs font-semibold shadow-lg"
                    :class="messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'"
                    x-text="message"
                ></div>
            </div>
    </form>
</div>
<?php if($client->current_membership): ?>
        <div class="border border-stone-200 rounded-lg px-4 py-3 bg-white space-y-2 text-xs text-stone-600">
            <p class="font-semibold text-stone-700 uppercase tracking-wide">Quota <?php echo e($client->current_membership->season_start_year); ?>/<?php echo e($client->current_membership->season_start_year + 1); ?></p>
            <div class="flex flex-wrap items-center gap-3">
                <span>Scadenza: <strong><?php echo e(optional($client->current_membership->due_date)->format('d/m/Y') ?? '—'); ?></strong></span>
                <span>Importo: <strong>€ <?php echo e(number_format($client->current_membership->amount ?? 0, 2, ',', '.')); ?></strong></span>
                <span>Stato: <strong><?php echo e(ucfirst($client->current_membership->status)); ?></strong></span>
                <span>Pagato il: <strong><?php echo e(optional($client->current_membership->paid_at)->format('d/m/Y H:i') ?? '—'); ?></strong></span>
            </div>
            <?php if(optional($client->membership_payment)?->receipt_url): ?>
                <div class="mt-2">
                    <a href="<?php echo e(route('admin.payments.receipt', optional($client->membership_payment)->id)); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        Scarica ricevuta
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php
                                                $adminPaymentsAll = $client->getAttribute('admin_payments_all');
                                                $adminPaymentsAllArray = $adminPaymentsAll instanceof \Illuminate\Support\Collection ? $adminPaymentsAll->values()->toArray() : [];
                                                $currentCalendarYear = now()->year;
                                            ?>
                                            <div
                                                x-data="{
                                                    showAll: false,
                                                    sortField: 'created_at',
                                                    sortDirection: 'desc',
                                                    currentYear: <?php echo e($currentCalendarYear); ?>,
                                                    paymentsAll: <?php echo \Illuminate\Support\Js::from($adminPaymentsAllArray)->toHtml() ?>,
                                                    get historyAvailable() {
                                                        return this.paymentsAll.some(payment => (payment.year ?? null) !== this.currentYear);
                                                    },
                                                    get dataset() {
                                                        const source = this.showAll
                                                            ? this.paymentsAll
                                                            : this.paymentsAll.filter(payment => (payment.year ?? null) === this.currentYear);
                                                        const direction = this.sortDirection === 'asc' ? 1 : -1;
                                                        const field = this.sortField;

                                                        return [...source].sort((a, b) => {
                                                            if (field === 'amount') {
                                                                return ((a.amount ?? 0) - (b.amount ?? 0)) * direction;
                                                            }

                                                            const aValue = a[field] ?? '';
                                                            const bValue = b[field] ?? '';

                                                            if (field === 'created_at') {
                                                                if (aValue === bValue) {
                                                                    return 0;
                                                                }

                                                                return (aValue > bValue ? 1 : -1) * direction;
                                                            }

                                                            return String(aValue).localeCompare(String(bValue), 'it', { numeric: true, sensitivity: 'base' }) * direction;
                                                        });
                                                    },
                                                    toggleSort(field) {
                                                        if (this.sortField === field) {
                                                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                                                        } else {
                                                            this.sortField = field;
                                                            this.sortDirection = field === 'created_at' ? 'desc' : 'asc';
                                                        }
                                                    },
                                                    sortIndicator(field) {
                                                        if (this.sortField !== field) {
                                                            return '⇅';
                                                        }

                                                        return this.sortDirection === 'asc' ? '↑' : '↓';
                                                    }
                                                }"
                                                class="w-full border border-stone-200 rounded-lg px-4 py-4 bg-white space-y-3 text-xs text-stone-600"
                                            >
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <p class="text-xs uppercase text-stone-500 font-semibold">Pagamenti</p>
                                                        <p class="text-sm text-stone-600" x-text="showAll ? 'Storico completo' : `Anno ${currentYear}`"></p>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-stone-500" x-text="`${dataset.length} risultati`"></span>
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 transition hover:bg-stone-100"
                                                            x-show="historyAvailable"
                                                            x-cloak
                                                            @click="showAll = !showAll"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                            <span x-text="showAll ? 'Mostra anno corrente' : 'Mostra storico completo'"></span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div
                                                    class="overflow-hidden rounded-lg border border-stone-200"
                                                    x-show="dataset.length > 0"
                                                    x-cloak
                                                >
                                                    <table class="min-w-full divide-y divide-stone-200 text-xs leading-tight">
                                                        <thead class="bg-stone-100 text-[11px] uppercase tracking-wider text-stone-500">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('created_at')">
                                                                        Data
                                                                        <span class="text-[10px]" x-text="sortIndicator('created_at')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('type_label')">
                                                                        Tipo
                                                                        <span class="text-[10px]" x-text="sortIndicator('type_label')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('amount')">
                                                                        Importo
                                                                        <span class="text-[10px]" x-text="sortIndicator('amount')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('status_label')">
                                                                        Stato
                                                                        <span class="text-[10px]" x-text="sortIndicator('status_label')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">
                                                                    <button type="button" class="flex items-center gap-1" @click="toggleSort('due_date')">
                                                                        Scadenza
                                                                        <span class="text-[10px]" x-text="sortIndicator('due_date')"></span>
                                                                    </button>
                                                                </th>
                                                                <th class="px-3 py-2 text-left font-semibold">Azioni</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-stone-100 bg-white">
                                                            <template x-for="payment in dataset" :key="`payment-${payment.id}`">
                                                                <tr class="hover:bg-stone-50" x-data="{ showWaiveForm: false }">
                                                                    <td class="px-3 py-2 align-top">
                                                                        <p class="text-sm font-semibold text-stone-800" x-text="payment.created_at_display ?? '—'"></p>
                                                                        <p class="text-[11px] text-stone-400" x-text="payment.year ? `Anno ${payment.year}` : ''"></p>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="text-sm font-semibold text-stone-800" x-text="payment.type_label"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="font-semibold text-stone-800">€ <span x-text="payment.amount_formatted"></span></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="payment.status_badge_class" x-text="payment.status_label"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <span x-text="payment.due_date_display ?? '—'"></span>
                                                                    </td>
                                                                    <td class="px-3 py-2 align-top">
                                                                        <div class="space-y-2">
                                                                            <div class="flex flex-wrap items-center gap-2">
                                                                                <template x-if="payment.routes.receipt">
                                                                                    <a
                                                                                        :href="payment.routes.receipt"
                                                                                        target="_blank"
                                                                                        rel="noopener"
                                                                                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-2 py-1 text-[10px] font-semibold text-white hover:bg-teal-700"
                                                                                    >
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                                                                                        </svg>
                                                                                        Ricevuta
                                                                                    </a>
                                                                                </template>
                                                                                <template x-if="payment.receipt_available && payment.routes.reprint">
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.reprint"
                                                                                        class="inline-flex"
                                                                                        onsubmit="return confirm('Confermi di ristampare la ricevuta? Il documento esistente verrà archiviato.');"
                                                                                    >
                                                                                        <?php echo csrf_field(); ?>
                                                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-2 py-1 text-[10px] font-semibold text-stone-600 hover:bg-stone-100 transition-colors">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7v6a2 2 0 01-2 2H9l-4 4V9a2 2 0 012-2h2"/>
                                                                                            </svg>
                                                                                            Ristampa
                                                                                        </button>
                                                                                    </form>
                                                                                </template>
                                                                                <template x-if="payment.is_pending">
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.update"
                                                                                        class="inline-flex"
                                                                                        onsubmit="return confirm('Confermi di registrare questo pagamento in contanti?');"
                                                                                    >
                                                                                        <?php echo csrf_field(); ?>
                                                                                        <input type="hidden" name="action" value="cash">
                                                                                        <input type="hidden" name="reason" value="">
                                                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10c1.486 0-2.737.81-2.959 1.893M12 6c-1.486 0-2.737.81-2.959 1.893M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                            </svg>
                                                                                            Paga in contanti
                                                                                        </button>
                                                                                    </form>
                                                                                </template>
                                                                            </div>
                                                                            <template x-if="payment.is_pending && payment.is_course">
                                                                                <div class="space-y-2">
                                                                                    <button
                                                                                        type="button"
                                                                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-stone-200 px-3 py-1.5 text-[11px] font-semibold text-stone-700 hover:bg-stone-300 transition-colors"
                                                                                        @click="showWaiveForm = !showWaiveForm"
                                                                                    >
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                        </svg>
                                                                                        <span x-text="showWaiveForm ? 'Nascondi annulla' : 'Annulla mese'"></span>
                                                                                    </button>
                                                                                    <form
                                                                                        method="POST"
                                                                                        :action="payment.routes.update"
                                                                                        class="space-y-2"
                                                                                        x-show="showWaiveForm"
                                                                                        x-cloak
                                                                                        onsubmit="return confirm('Confermi di annullare il mese per questa allieva/o?');"
                                                                                    >
                                                                                        <?php echo csrf_field(); ?>
                                                                                        <input type="hidden" name="action" value="waive">
                                                                                        <textarea name="reason" rows="2" class="input-field text-xs" placeholder="Motivo (es. malattia)" required></textarea>
                                                                                        <div class="flex items-center gap-2">
                                                                                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-teal-600 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-teal-700 transition-colors">
                                                                                                Conferma annulla
                                                                                            </button>
                                                                                            <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-stone-100 px-2.5 py-1.5 text-[11px] font-semibold text-stone-600 hover:bg-stone-200" @click="showWaiveForm = false">
                                                                                                Annulla
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div
                                                    class="rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-500 space-y-2"
                                                    x-show="dataset.length === 0"
                                                    x-cloak
                                                >
                                                    <p x-text="showAll ? 'Nessun pagamento registrato per questo allieva/o.' : `Nessun pagamento registrato per l\'anno ${currentYear}.`"></p>
                                                    <template x-if="!showAll && historyAvailable">
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                                                            @click="showAll = true"
                                                        >
                                                            Visualizza storico completo
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Nessun allieva/o registrato al momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div><?php /**PATH /Users/vincenzo/Documents/yoga-studio-erp/resources/views/admin/clients/partials/management.blade.php ENDPATH**/ ?>