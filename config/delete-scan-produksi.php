<?php
// config.php - Konfigurasi database dan koneksi
return [
    // Konfigurasi MongoDB
    'mongoHost' => env('DELETE_SCAN_MONGO_HOST', 'localhost'),
    'mongoPort' => env('DELETE_SCAN_MONGO_PORT', '27017'),
    'mongoDatabase' => env('DELETE_SCAN_MONGO_DATABASE', 'sdi_testing'),
    'mongoCollection' => env('DELETE_SCAN_MONGO_COLLECTION', 'm_kanban_generate_details'),
    
    // Field yang akan di-set ke null
    'fieldsToNullify' => [
        "parent_item",
        "scan_produksi_id",
        "scanned_produksi_at",
        "status_submit_produksi",
        "lot_prod",
        "submit_produksi_at"
    ],
    
    // Konfigurasi SSH (opsional)
    'ssh' => [
        'enabled' => env('DELETE_SCAN_SSH_ENABLED', false),
        'host' => env('DELETE_SCAN_SSH_HOST', '38.47.67.48'),
        'port' => env('DELETE_SCAN_SSH_PORT', 22),
        'user' => env('DELETE_SCAN_SSH_USER', 'ekanban'),
        'password' => env('DELETE_SCAN_SSH_PASSWORD', 'Sdi@2022'),
    ]
];