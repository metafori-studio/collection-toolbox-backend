<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend Application URL
    |--------------------------------------------------------------------------
    |
    | This value is the base URL of your frontend application, which is used
    | when generating links that should point to the frontend rather than
    | this backend application.
    |
    */

    'url' => env('FRONTEND_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | Frontend Routes
    |--------------------------------------------------------------------------
    |
    | Here you may define the routes for your frontend application. This allows
    | you to centrally manage all frontend URLs that are referenced from your
    | Laravel application.
    |
    */

    'routes' => [
        'reset_password' => '/reset-password',
        'set_password' => '/set-password',
    ],

];
