<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class PosCartOrderTest extends TestCase
{
    public function test_cart_items_are_normalized_in_the_order_received_from_frontend(): void
    {
        $frontendCart = [
            2 => [
                'product_id' => 2,
                'name' => 'Second Item',
                'price' => 20,
                'quantity' => 1,
                'sort_order' => 2,
            ],
            1 => [
                'product_id' => 1,
                'name' => 'First Item',
                'price' => 10,
                'quantity' => 2,
                'sort_order' => 1,
            ],
            3 => [
                'product_id' => 3,
                'name' => 'Third Item',
                'price' => 30,
                'quantity' => 1,
                'sort_order' => 3,
            ],
        ];

        $orderedCart = [];
        $cart = [];
        foreach ($frontendCart as $key => $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? $key;
            $normalizedItem = [
                'product_id' => $productId,
                'name' => $item['name'] ?? '',
                'price' => (float)($item['price'] ?? 0),
                'quantity' => (int)($item['quantity'] ?? 1),
                'sort_order' => (int)($item['sort_order'] ?? 0),
            ];

            $cart[$productId] = $normalizedItem;
            $orderedCart[] = $normalizedItem;
        }

        usort($orderedCart, function ($first, $second) {
            return ($first['sort_order'] ?? 0) <=> ($second['sort_order'] ?? 0);
        });

        $names = array_column($orderedCart, 'name');

        $this->assertSame(['First Item', 'Second Item', 'Third Item'], $names);
    }
}
