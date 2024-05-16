<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Odoo Host
    | Must start with http:// or https:// and include port
    |--------------------------------------------------------------------------
    */
    'host' => env('ODOO_HOST','http://10.254.222.80:8069'),

    /*
    |--------------------------------------------------------------------------
    | Database name
    |--------------------------------------------------------------------------
    */
    'database' => env('ODOO_DATABASE','beras_erp_dev'),

    /*
    |--------------------------------------------------------------------------
    | User name
    |--------------------------------------------------------------------------
    */
    'username' => env('ODOO_USERNAME', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | User password
    |--------------------------------------------------------------------------
    */
    'password' => env('ODOO_PASSWORD', 'admin'),

    'context' => [
        'lang' => env('ODOO_LANG', null),
        'timezone' => env('ODOO_TIMEZONE', null),
        'companyId' => env('ODOO_COMPANY_ID', null),
    ]
];