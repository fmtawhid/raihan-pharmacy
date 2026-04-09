#!/bin/bash
cd "c:\laragon\www\raihan-pharmacy\core"
cat << 'EOF' | php artisan tinker
$admin = \App\Models\Admin::find(1); // Super Admin
echo "Admin: " . $admin->name . "\n";
echo "Has role 'admin': " . ($admin->hasRole('admin') ? 'YES' : 'NO') . "\n";
echo "\nPermissions:\n";
$admin->getAllPermissions()->each(function($p) { echo "- " . $p->name . "\n"; });
echo "\nHas salse_dashboard: " . ($admin->hasPermissionTo('salse_dashboard', 'admin') ? 'YES' : 'NO') . "\n";
echo "Has summery_dashboard: " . ($admin->hasPermissionTo('summery_dashboard', 'admin') ? 'YES' : 'NO') . "\n";
EOF
