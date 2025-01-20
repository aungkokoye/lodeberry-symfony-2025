<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartSessionStorage
{
    const CART_KEY = 'loadberry-cart';


    public function __construct(private RequestStack $requestStack)
    {
    
    }

    public function getCart(): ?array
    {
        return $this->getRequest()->getSession()->get(self::CART_KEY);
    }

    private function setCart(array $cart): void
    {
        $this->getRequest()->getSession()->set(self::CART_KEY, $cart);
    }


    public function setItem(int $productID, int $quantity): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productID])) {
            $cartQuantity = $cart[$productID];
            $updatedQuantity = $cartQuantity + $quantity;
            if ($updatedQuantity <= 3 && $updatedQuantity >= 0) {
                $cart[$productID] = $updatedQuantity;
                $this->setCart($cart);
            }
        } else {
            if ($quantity === 1) {
                $cart[$productID] = $quantity;
            }
        }

        $this->setCart($cart);
    }

    public function removeItem(int $productID)
    {
        $cart = $this->getCart();
        if (is_array($cart)) {
            unset($cart[$productID]);
        }
        
        $this->setCart($cart);
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
        $this->getRequest()->getSession()->remove(self::CART_KEY);
    }

    public function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}