<?php

return [
    'template_path' => resource_path('receipts/template.json'),
    'storage_disk' => 'public',
    'storage_directory' => 'receipts',
    'currency_symbol' => '€',
    'owner_password' => env('RECEIPT_OWNER_PASSWORD', 'ShantiSadhana2025!'),
    'user_password' => env('RECEIPT_USER_PASSWORD'),
];
