<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Print Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default print driver that will be used by the
    | application. You may set this to any of the drivers defined below.
    |
    | Supported: "escpos", "pdf", "null"
    |
    */

    'default' => env('PRINT_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Print Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the print drivers for your application.
    |
    */

    'drivers' => [

        'escpos' => [
            'driver' => 'escpos',
            'ip' => env('PRINTER_IP', '192.168.1.100'),
            'port' => env('PRINTER_PORT', 9100),
            'timeout' => 30,
        ],

        'pdf' => [
            'driver' => 'pdf',
            'paper' => 'a4',
            'orientation' => 'portrait',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for receipt printing
    |
    */

    'receipt' => [
        'width' => 48, // Characters per line for thermal printer
        'logo_path' => storage_path('app/public/logo.png'),
        'footer_text' => 'Terima kasih atas kunjungan Anda!',
        'contact_info' => [
            'phone' => '(021) 1234-5678',
            'address' => 'Jl. Contoh No. 123, Jakarta',
        ],
    ],

];
