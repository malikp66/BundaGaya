<?php

return [
    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY', ''),
        'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),
    ],

    'warehouse' => [
        'latitude' => env('WAREHOUSE_LATITUDE', '-6.303543'),
        'longitude' => env('WAREHOUSE_LONGITUDE', '106.648257'),
        'postal_code' => env('WAREHOUSE_POSTAL_CODE', '15331'),
        'address' => env('WAREHOUSE_ADDRESS', 'BSD City'),
        'contact_name' => env('WAREHOUSE_CONTACT_NAME', 'BundaGaya Warehouse'),
        'contact_phone' => env('WAREHOUSE_CONTACT_PHONE', ''),
        'note' => env('WAREHOUSE_NOTE', ''),
        'city' => 'Tangerang Selatan',
        'province' => 'Banten',
    ],
];
