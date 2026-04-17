<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External db:seed query password
    |--------------------------------------------------------------------------
    |
    | Used by GET /db/seed?pass=… and GET /db/migrate?pass=…
    | Default pass is 1234. Override with OPTIC_DB_SEED_PASS in .env if needed.
    | Seed optional: &class=YourSeeder (under Database\Seeders).
    |
    */
    'db_seed_pass' => env('OPTIC_DB_SEED_PASS', '1234'),

];
