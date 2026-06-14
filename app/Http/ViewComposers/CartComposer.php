<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Services\V1\Core\CartService;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartComposer
{

    protected $cartService;
    protected static ?array $cartData = null;

    public function __construct(
        CartService $cartService,
    ) {
        $this->cartService = $cartService;
    }

    public function compose(View $view)
    {

        if (static::$cartData === null) {
            $carts = Cart::instance('shopping')->content();
            $this->cartService->remakeCart($carts);

            static::$cartData = [
                'cartShare' => $this->cartService->reCaculateCart(),
                'wishlistCount' => Cart::instance('wishlist')->count(),
                'compareCount' => Cart::instance('compare')->count(),
            ];
        }

        $view->with(static::$cartData);
    }
}
