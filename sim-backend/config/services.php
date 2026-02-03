<?php

return [
    'detran' => [
        'url' => env('DETRAN_API_URL', 'https://api.detran.sp.gov.br'),
        'token' => env('DETRAN_API_TOKEN', ''),
        'timeout' => env('DETRAN_API_TIMEOUT', 30),
    ],
];
