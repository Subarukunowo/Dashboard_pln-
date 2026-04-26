<?php

// config/gas.php
// Akses via config('gas.data_url') atau env('GAS_DATA_URL')

return [

    /*
    |--------------------------------------------------------------------------
    | Google Apps Script URLs
    |--------------------------------------------------------------------------
    | URL diambil dari .env — tidak pernah hardcode di JS atau kode PHP.
    */

    'data_url' => env('GAS_DATA_URL', ''),

    'historis_url' => env('DATA_HISTORIS_URL', ''),

    'timeout' => (int) env('PLN_API_TIMEOUT', 60),

];