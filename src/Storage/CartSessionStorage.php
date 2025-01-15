<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartSessionStorage
{
    const CART_KEY = 'loadberry-cart';

    private ?Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getCart(): ?array
    {
        return $this->request->getSession()->get(self::CART_KEY);
    }

    private function setCart(array $cart): void
    {
        $this->request->getSession()->set(self::CART_KEY, $cart);
    }


    public function setItem(int $productID, int $quantity): void
    {
        $cart = $this->getCart();

        if (!is_array($cart)) {
            $this->setCart([$productID => $quantity]);
        } else {
            $cart[$productID] = isset($cart[$productID]) ? $cart[$productID] + $quantity : $quantity;
            $this->setCart($cart);
        }
    }

    public function removeItem(int $productID)
    {
        $cart = $this->getCart();
        if (is_array($cart)) {
            unset($cart[$productID]);
        }
    }

    public function getItemQuantity(int $productID): int
    {
        $cart = $this->getCart();

        if (!is_array($cart)) {
            return 0;
        }

        return $cart[$productID] ?? 0;
    }

    public function destroyShoppingCart(): void
    {
        $this->request->getSession()->remove(self::CART_KEY);
    }
}