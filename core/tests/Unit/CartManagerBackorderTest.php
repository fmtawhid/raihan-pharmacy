<?php

namespace Tests\Unit;

use App\Lib\CartManager;
use PHPUnit\Framework\TestCase;

class CartManagerBackorderTest extends TestCase
{
    public function test_website_cart_can_accept_inventory_tracked_products_as_backorders(): void
    {
        $product = new class {
            public $is_downloadable = false;

            public function trackInventory($variant): bool
            {
                return true;
            }
        };

        $cartManager = new CartManager();

        $this->assertNull($cartManager->checkCartQuantity($product, null, 0, 1, true));
    }

    public function test_stock_limited_cart_behavior_remains_available_by_default(): void
    {
        $product = new class {
            public $is_downloadable = false;

            public function trackInventory($variant): bool
            {
                return true;
            }
        };

        $cartManager = new CartManager();

        $this->assertSame(
            ['error' => 'Requested quantity is not available in our stock'],
            $cartManager->checkCartQuantity($product, null, 0, 1)
        );
    }
}
