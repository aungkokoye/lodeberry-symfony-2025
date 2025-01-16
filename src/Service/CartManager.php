<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Storage\CartSessionStorage;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductOrder;
use App\Exception\CartManagerException;
use App\Repository\ProductRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Ramsey\Uuid\Uuid;

class CartManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ProductRepository $productRepository,
        private CartSessionStorage $cartSessionStorage,
        private Security $security
    )
    {

    }

    public function handleCartSubmit(Order $order) : void
    {
        $user = $this->security->getUser();

        if(!$user) {
            throw new CartManagerException('Login user not found!');
        }
        
        $current = new \DateTime();
        $cart = $this->cartSessionStorage->getCart();

        if (!empty($cart)) {
            $order->setUuid(Uuid::uuid4()->toString())
                ->setOrderBy($user)
                ->setCreatedAt($current)
                ->setUpdatedAt($current)
                ->setStatus(Order::ORDER_CREATE_STATUS)
            ;
            $totalItems = 0;
            foreach ($cart as $productId => $quantity) {
                if ($quantity > 0) {
                    $totalItems += $quantity;
                    $product = $this->productRepository->find($productId);
                    if ( $product instanceof Product) {
                            $productOrder = new ProductOrder();
                            $productOrder->setProduct($product)
                                        ->setOrderRef($order)
                                        ->setQuantity($quantity)
                            ;
                            $order->addProductOrder($productOrder);
                        }
                        
                }
            }

            if ($totalItems > 0) {
                $this->em->persist($order);
                $this->em->flush();
            }
        }
    }
}