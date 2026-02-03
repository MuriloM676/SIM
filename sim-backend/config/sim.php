<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurações do Sistema SIM
    |--------------------------------------------------------------------------
    */

    // Upload de evidências
    'evidencias' => [
        'max_size_kb' => env('SIM_MAX_FILE_SIZE', 10240), // 10MB
        'allowed_types' => explode(',', env('SIM_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf')),
        'storage_disk' => 'local',
        'storage_path' => 'evidencias',
    ],

    // Auditoria
    'auditoria' => [
        'retention_days' => env('SIM_AUDITORIA_RETENTION_DAYS', 3650), // 10 anos
        'log_data_views' => true, // Loga visualização de dados (LGPD)
    ],

    // Multas
    'multas' => [
        'prazo_recurso_dias' => env('SIM_MULTA_PRAZO_RECURSO_DIAS', 30),
        'auto_infracao_prefix' => 'AI',
    ],

    // Integrações
    'detran' => [
        'enabled' => env('DETRAN_INTEGRATION_ENABLED', true),
        'retry_attempts' => 3,
        'retry_delay' => [60, 300, 900], // segundos
    ],

];
