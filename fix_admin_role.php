<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin@100x.com')->first();
if ($user) {
    echo "User found: " . $user->name . "\n";
    echo "Role: " . $user->role . "\n";
    
    if ($user->role !== 'admin') {
        echo "Updating role to admin...\n";
        $user->role = 'admin';
        $user->save();
        echo "Role updated!\n";
    } else {
        echo "Role is already admin.\n";
    }
} else {
    echo "User admin@100x.com not found.\n";
}
