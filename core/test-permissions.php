<?php
// File: core/test-permissions.php
// Test if permissions are loaded correctly

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Get the admin user
$admin = \App\Models\Admin::where('username', 'admin')->first();

if (!$admin) {
    echo "❌ Admin user not found!\n";
    exit;
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Permission Check for Admin: " . $admin->name . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Get all roles
$roles = $admin->getRoles()->toArray();
echo "\n✓ Admin Roles: \n";
foreach ($roles as $role) {
    echo "  - " . $role['name'] . " (ID: " . $role['id'] . ")\n";
}

// Check specific permissions
$permissionsToCheck = ['summery_dashboard', 'salse_dashboard', 'view_dashboard'];

echo "\n✓ Permission Check Results:\n";
foreach ($permissionsToCheck as $permission) {
    $hasPermission = $admin->can($permission);
    $status = $hasPermission ? '✓' : '✗';
    echo "  $status $permission: " . ($hasPermission ? 'ALLOWED' : 'DENIED') . "\n";
}

// Get all permissions
$allPermissions = $admin->getAllPermissions();
echo "\n✓ Total Permissions Assigned: " . count($allPermissions) . "\n";

// Show all permissions
echo "\nAll Assigned Permissions:\n";
$permissions = $allPermissions->groupBy(function ($permission) {
    return substr($permission['name'], 0, 4);
});

foreach ($permissions as $group => $perms) {
    echo "  [$group*] " . count($perms) . " permissions\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Cache Status: " . (\Cache::has('spatie.permission.cache') ? 'CACHED' : 'NOT CACHED') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>
