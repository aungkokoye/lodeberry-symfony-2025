<?php

namespace App\Tests\Integration\Storage;

use App\Storage\CartSessionStorage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CartSessionStorageTest extends KernelTestCase 
{

    protected CartSessionStorage $cartSessionStorage;

    protected function setUp(): void
    {
        $storage = new MockArraySessionStorage();
        $session = new Session($storage);

        $request = $this->createMock(Request::class);
        $request->expects(self::any())
        ->method('getSession')
        ->willReturn($session);
        
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::any())
        ->method('getCurrentRequest')
        ->willReturn($request);

        $container = static::getContainer();
        $container->set(RequestStack::class, $requestStack);

        $this->cartSessionStorage = $container->get(CartSessionStorage::class);
    }
    
    public function testGetCart(): void
    {
        self::bootKernel();
        $this->cartSessionStorage->setItem(1, 1);

        $this->assertEquals([1 => 1], $this->cartSessionStorage->getCart());        
    }

    public function testGetItemQuantity(): void
    {
        self::bootKernel();

        $this->cartSessionStorage->setItem(2, 1);
        $this->assertEquals(1, $this->cartSessionStorage->getItemQuantity(2));
        
        $this->cartSessionStorage->setItem(2, 2);
        $this->assertEquals(3, $this->cartSessionStorage->getItemQuantity(2));

        /* max limite reach */
        $this->cartSessionStorage->setItem(2, 1);
        $this->assertEquals(3, $this->cartSessionStorage->getItemQuantity(2));

        $this->cartSessionStorage->setItem(2, -1);
        $this->assertEquals(2, $this->cartSessionStorage->getItemQuantity(2));

        /* min limte reach */
        $this->cartSessionStorage->setItem(2, -3);
        $this->assertEquals(2, $this->cartSessionStorage->getItemQuantity(2));
    }

    public function testRemoveAndDestory(): void
    {
        self::bootKernel();

         /* Remove Item */
        $this->cartSessionStorage->setItem(2, 1); 
        $this->cartSessionStorage->removeItem(2);
        $this->assertEquals( 0, $this->cartSessionStorage->getItemQuantity(2));

         /* Destory Cart */
        $this->cartSessionStorage->setItem(1, 1);
        $this->cartSessionStorage->setItem(3, 1);
        $this->cartSessionStorage->destroyShoppingCart();

        $this->assertEquals( 0, $this->cartSessionStorage->getItemQuantity(2));
        $this->assertEquals( 0, $this->cartSessionStorage->getItemQuantity(3));
        $this->assertEquals( 0, $this->cartSessionStorage->getCart());
    }
}