<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Das User-Model, das für die author()-Beziehung in Posts verwendet wird.
    |
    */
    'user_model' => env('BLOG_USER_MODEL', 'App\\Models\\User'),
];
