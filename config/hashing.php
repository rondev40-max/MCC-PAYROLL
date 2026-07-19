<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'argon',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Hashing Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the cost settings used by the Bcrypt algorithm
    | when hashing passwords. This value determines how many rounds of
    | processing are applied to the given password hashing function.
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Hashing Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the memory and time cost settings used by the
    | Argon2 algorithm when hashing passwords. These values determine
    | the required memory and time to hash the given password.
    |
    */

    'argon' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon2id Hashing Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the memory and time cost settings used by the
    | Argon2id algorithm when hashing passwords. These values determine
    | the required memory and time to hash the given password.
    |
    */

    'argon2id' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
        'verify' => true,
    ],

];
