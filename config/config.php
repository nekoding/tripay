<?php

/*
 * You can place your custom package configuration in here.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | TRIPAY API MODE
    |--------------------------------------------------------------------------
    |
    | Secara default API yang digunakan adalah API sandbox, jika ingin
    | menggunakan API Production ubah value TRIPAY_API_PRODUCTION menjadi
    | `true`
    */
    'tripay_api_production' => env('TRIPAY_API_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | TRIPAY API KEY
    |--------------------------------------------------------------------------
    |
    | Masukkan API KEY yang anda peroleh dari pihak tripay. API KEY perlu
    | diisi agar dapat menggunakan fitur - fitur dari tripay.
    */
    'tripay_api_key' => env('TRIPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | TRIPAY PRIVATE KEY
    |--------------------------------------------------------------------------
    |
    | Masukkan PRIVATE KEY yang anda peroleh dari pihak tripay. PRIVATE KEY
    | diperlukan dalam proses pembuatan signature key.
    */
    'tripay_private_key' => env('TRIPAY_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | TRIPAY API MERCHANT CODE
    |--------------------------------------------------------------------------
    |
    | Masukkan MERCHANT CODE yang anda peroleh dari pihak tripay. MERCHANT CODE
    | diperlukan dalam proses pembuatan signature key.
    */
    'tripay_merchant_code' => env('TRIPAY_MERCHANT_CODE')
];
