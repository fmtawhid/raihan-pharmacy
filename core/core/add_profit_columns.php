<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n=== Adding Profit Tracking Columns ===\n\n";

// Add columns to order_details table
if (!Schema::hasColumn('order_details', 'purchase_price')) {
    DB::statement('ALTER TABLE order_details ADD COLUMN purchase_price DOUBLE DEFAULT 0 AFTER price');
    echo "✓ Added purchase_price to order_details\n";
} else {
    echo "✓ purchase_price already exists in order_details\n";
}

if (!Schema::hasColumn('order_details', 'batch_id')) {
    DB::statement('ALTER TABLE order_details ADD COLUMN batch_id BIGINT UNSIGNED NULL AFTER purchase_price');
    echo "✓ Added batch_id to order_details\n";
} else {
    echo "✓ batch_id already exists in order_details\n";
}

if (!Schema::hasColumn('order_details', 'profit')) {
    DB::statement('ALTER TABLE order_details ADD COLUMN profit DOUBLE NULL AFTER batch_id');
    echo "✓ Added profit to order_details\n";
} else {
    echo "✓ profit already exists in order_details\n";
}

// Add columns to orders table
if (!Schema::hasColumn('orders', 'cost_amount')) {
    DB::statement('ALTER TABLE orders ADD COLUMN cost_amount DOUBLE DEFAULT 0 AFTER total_amount');
    echo "✓ Added cost_amount to orders\n";
} else {
    echo "✓ cost_amount already exists in orders\n";
}

if (!Schema::hasColumn('orders', 'profit')) {
    DB::statement('ALTER TABLE orders ADD COLUMN profit DOUBLE DEFAULT 0 AFTER cost_amount');
    echo "✓ Added profit (column) to orders\n";
} else {
    echo "✓ profit already exists in orders\n";
}

echo "\n✓ Database schema updated successfully!\n\n";
