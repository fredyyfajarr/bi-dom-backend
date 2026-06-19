<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'manager@dom.com')->first();
if ($user) {
    echo "User found: " . $user->name . " (Role: " . $user->role . ")\n";
    echo "Password check: " . (Hash::check('password123', $user->password) ? 'MATCH' : 'FAIL') . "\n";
} else {
    echo "User manager@dom.com NOT FOUND in database.\n";
}
