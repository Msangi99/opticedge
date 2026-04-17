<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External db:seed query password
    |--------------------------------------------------------------------------
    |
    | Call: GET /db/seed?pass=YOUR_VALUE
    | Default pass is 1234. Override with OPTIC_DB_SEED_PASS in .env if needed.
    | Optional: &class=YourSeeder (under Database\Seeders).
    |
    */
    'db_seed_pass' => env('OPTIC_DB_SEED_PASS', '1234'),

];
