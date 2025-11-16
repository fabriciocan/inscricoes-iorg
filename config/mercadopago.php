<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Public Key
    |--------------------------------------------------------------------------
    |
    | This is your Mercado Pago public key used for client-side operations.
    |
    */

    'public_key' => env('MERCADOPAGO_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Access Token
    |--------------------------------------------------------------------------
    |
    | This is your Mercado Pago access token used for server-side operations.
    |
    */

    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', ''),

];
