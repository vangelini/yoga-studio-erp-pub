<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo e(config('app.name', 'Shanti Sadhana')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/alpine.js']); ?>
        <style>
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            ::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
        <?php echo $__env->yieldPushContent('head'); ?>
    </head>
    <body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">
        <?php echo $__env->yieldContent('body'); ?>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\yoga-studio-erp\resources\views/layouts/base.blade.php ENDPATH**/ ?>