<?php
// Quick cache clearing script
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Clear all caches
$kernel->call('cache:clear');
$kernel->call('config:clear');
$kernel->call('permission:cache-reset');
$kernel->call('config:cache');

echo "✓ All caches cleared successfully!\n";
echo "✓ Time: " . date('Y-m-d H:i:s') . "\n";
?>
