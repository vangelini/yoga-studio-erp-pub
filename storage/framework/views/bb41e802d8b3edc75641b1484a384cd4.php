<?php $__env->startSection('body'); ?>
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-emerald-50 py-16 px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10 items-start">
            <div class="lg:col-span-2 bg-white/70 backdrop-blur-sm border border-teal-100 shadow-xl rounded-3xl p-8 flex flex-col gap-6">
                <div class="space-y-4">
                    <img src="<?php echo e(asset('images/yoga-logo-big.jpg')); ?>" alt="Shanti Sadhana Logo" class="w-24 h-24 rounded-full shadow-md border border-white/70">
                    <h1 class="text-3xl font-semibold text-stone-900">Diventa parte di Shanti Sadhana</h1>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Completa il modulo per creare il tuo account cliente. Un amministratore approverà la tua iscrizione prima che tu possa prenotare lezioni.
                    </p>
                </div>
                <div class="bg-gradient-to-r from-teal-100 to-emerald-100 text-teal-800 px-5 py-4 rounded-2xl border border-teal-200">
                    <p class="text-sm font-semibold">Hai già un account?</p>
                    <p class="text-sm text-teal-700 mt-1">
                        <a href="<?php echo e(route('login')); ?>" class="underline decoration-teal-600 decoration-2 hover:text-teal-900">
                            Accedi dalla pagina di login.
                        </a>
                    </p>
                </div>
            </div>

            <?php
                $phonePrefixes = [
                    ['code' => '+39', 'name' => 'Italia'],
                    ['code' => '+33', 'name' => 'Francia'],
                    ['code' => '+49', 'name' => 'Germania'],
                    ['code' => '+34', 'name' => 'Spagna'],
                    ['code' => '+44', 'name' => 'Regno Unito'],
                    ['code' => '+1', 'name' => 'Stati Uniti'],
                ];
            ?>

            <div class="lg:col-span-3 bg-white shadow-xl border border-stone-200/80 rounded-3xl p-8">
                <form
                    method="POST"
                    action="<?php echo e(route('register.attempt')); ?>"
                    class="space-y-6"
                    x-data="{
                        country: '<?php echo e(old('telephone_country', '+39')); ?>'
                    }"
                >
                    <?php echo csrf_field(); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-sm font-medium text-stone-600">Nome</label>
                            <input
                                type="text"
                                name="first_name"
                                value="<?php echo e(old('first_name')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Nome"
                            >
                            <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Cognome</label>
                            <input
                                type="text"
                                name="last_name"
                                value="<?php echo e(old('last_name')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Cognome"
                            >
                            <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-stone-600">Email</label>
                            <input
                                type="email"
                                name="email"
                                value="<?php echo e(old('email')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="you@example.com"
                            >
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium text-stone-600">Telefono</label>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <select
                                    name="telephone_country"
                                    x-model="country"
                                    required
                                    class="p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['telephone_country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                >
                                    <?php $__currentLoopData = $phonePrefixes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option['code']); ?>" <?php if(old('telephone_country', '+39') === $option['code']): echo 'selected'; endif; ?>>
                                            <?php echo e($option['name']); ?> (<?php echo e($option['code']); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="md:col-span-3 flex items-center gap-2">
                                    <span class="px-3 py-2 rounded-lg bg-stone-100 border border-stone-300 font-semibold text-stone-700" x-text="country"></span>
                                    <input
                                        type="tel"
                                        name="telephone"
                                        value="<?php echo e(old('telephone')); ?>"
                                        required
                                        class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        placeholder="Numero di telefono"
                                    >
                                </div>
                            </div>
                            <?php $__errorArgs = ['telephone_country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Password</label>
                            <input
                                type="password"
                                name="password"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Password"
                            >
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Conferma password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                placeholder="Ripeti password"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Codice fiscale</label>
                            <input
                                type="text"
                                name="codice_fiscale"
                                value="<?php echo e(old('codice_fiscale')); ?>"
                                maxlength="16"
                                pattern="[A-Za-z0-9]{16}"
                                oninput="this.value = this.value.toUpperCase()"
                                required
                                class="w-full mt-1 p-3 border rounded-lg uppercase focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['codice_fiscale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Codice fiscale"
                            >
                            <?php $__errorArgs = ['codice_fiscale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Luogo di nascita</label>
                            <input
                                type="text"
                                name="luogo_nascita"
                                value="<?php echo e(old('luogo_nascita')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['luogo_nascita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Città/Comune"
                            >
                            <?php $__errorArgs = ['luogo_nascita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Data di nascita</label>
                            <input
                                type="date"
                                name="data_nascita"
                                value="<?php echo e(old('data_nascita')); ?>"
                                max="<?php echo e(now()->subYears(16)->format('Y-m-d')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['data_nascita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            >
                            <?php $__errorArgs = ['data_nascita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Città di residenza</label>
                            <input
                                type="text"
                                name="residenza_citta"
                                value="<?php echo e(old('residenza_citta')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['residenza_citta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Città"
                            >
                            <?php $__errorArgs = ['residenza_citta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Provincia</label>
                            <input
                                type="text"
                                name="residenza_provincia"
                                value="<?php echo e(old('residenza_provincia')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['residenza_provincia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Provincia"
                            >
                            <?php $__errorArgs = ['residenza_provincia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Stato</label>
                            <input
                                type="text"
                                name="residenza_stato"
                                value="<?php echo e(old('residenza_stato')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['residenza_stato'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Italia"
                            >
                            <?php $__errorArgs = ['residenza_stato'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-stone-600">Indirizzo (via)</label>
                            <input
                                type="text"
                                name="residenza_via"
                                value="<?php echo e(old('residenza_via')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['residenza_via'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Via / Piazza"
                            >
                            <?php $__errorArgs = ['residenza_via'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-stone-600">Numero civico</label>
                            <input
                                type="text"
                                name="residenza_numero_civico"
                                value="<?php echo e(old('residenza_numero_civico')); ?>"
                                required
                                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 <?php $__errorArgs = ['residenza_numero_civico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Es. 12/B"
                            >
                            <?php $__errorArgs = ['residenza_numero_civico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <?php if(!empty($membership_config)): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 space-y-3 text-sm text-stone-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-stone-500">Quota associativa</p>
                                    <p class="text-base font-semibold text-stone-800">€ <?php echo e(number_format($membership_config['fee'] ?? 0, 2, ',', '.')); ?></p>
                                </div>
                                <span class="text-xs text-stone-500 font-semibold"><?php echo e($membership_config['duration_label'] ?? ''); ?></span>
                            </div>
                            <p class="text-xs text-stone-600"><?php echo e($membership_config['description'] ?? ''); ?></p>
                            <label class="inline-flex items-start gap-3 text-xs text-stone-600">
                                <input
                                    type="checkbox"
                                    name="membership_agreement"
                                    value="1"
                                    required
                                    <?php echo e(old('membership_agreement') ? 'checked' : ''); ?>

                                    class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-500"
                                >
                                <span>
                                    Confermo di aver preso visione dello Statuto dell'associazione, della relativa durata della quota associativa e di accettare l'importo previsto.
                                </span>
                            </label>
                            <?php $__errorArgs = ['membership_agreement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <div class="bg-stone-100 border border-stone-200 rounded-xl p-5 space-y-4">
                        <div>
                            <p class="text-sm text-stone-600 mb-3">
                                Prima di completare la registrazione, leggi i seguenti documenti:
                            </p>
                            <ul class="space-y-2 text-sm text-teal-700 font-semibold">
                                <li>
                                    <a
                                        href="<?php echo e(asset('Statuto associazione.pdf')); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="underline hover:text-teal-800"
                                    >
                                        Statuto dell'associazione
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="<?php echo e(asset('ShantiSadhana_InformativaPrivacy.pdf')); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="underline hover:text-teal-800"
                                    >
                                        Condizioni assicurative, scarico di responsabilità e trattamento dati personali
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <label class="inline-flex items-start gap-3 text-sm text-stone-600">
                            <input
                                type="checkbox"
                                name="statute_agreement"
                                value="1"
                                required
                                <?php echo e(old('statute_agreement') ? 'checked' : ''); ?>

                                class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-500"
                            >
                            <span>
                                Confermo di aver letto e accettato lo Statuto e le Condizioni assicurative, lo scarico di responsabilità e il trattamento dei dati personali.
                            </span>
                        </label>
                        <?php $__errorArgs = ['statute_agreement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-2"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <?php if(config('services.recaptcha.enabled') && config('services.recaptcha.site_key')): ?>
                        <div class="space-y-2">
                            <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                            <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-semibold py-3 rounded-lg hover:from-teal-700 hover:to-emerald-700 transition-colors shadow-md">
                        Completa iscrizione
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php if(config('services.recaptcha.enabled') && config('services.recaptcha.site_key')): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\yoga-studio-erp\resources\views/auth/register.blade.php ENDPATH**/ ?>