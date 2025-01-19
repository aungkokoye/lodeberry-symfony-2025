<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductOrder;
use App\Exception\CartManagerException;
use App\Form\CartCheckoutType;
use App\Repository\ProductOrderRepository;
use App\Repository\ProductRepository;
use App\Security\Voter\CartViewVoter;
use App\Service\CartManager;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ShoppingCartController extends AbstractController
{
    #[Route('/shopping-cart-set', name: 'app_shopping_cart_set', methods: ['POST'])]
    public function addItem(
        Request $request,
        ProductRepository $productRepository,
        CartSessionStorage $cartSessionStorage
    ):JsonResponse
    {
        $error = null;
        $data = json_decode($request->getContent(), true);
        $productID = (int) $data['id'];
        $add = $data['add'] ? 1 : -1;

        $product = $productRepository->find($productID);

        if ($product instanceof Product) {
            $quantity = $cartSessionStorage->getItemQuantity($productID);
            switch (true ) {
                case $quantity >= ProductOrder::MAX_QUANTITY && $add === 1:
                    $error = sprintf("Reach max quantity.  (Total: %d )",  ProductOrder::MAX_QUANTITY);
                    break;
                case $quantity === 0 && $add === -1:
                    $error = sprintf("Reach mix quantity.  (Total: %d )",  0);
                    break;
                default:
                    $error = null;

            }

        } else {
            $error = 'Product not found!';
        }

       if ($error === null) {
            $cartSessionStorage->setItem($productID, $add);
            $upadtedQuality = $cartSessionStorage->getItemQuantity($productID);
           return new JsonResponse([
               'status'             => 'success',
               'quantity'           => $upadtedQuality,
               'adjustToatlAmount'  => ($upadtedQuality - $quantity) * ($product->getPrice()),
               'message'            => sprintf(
                            "Successfully set to shopping cart. (Total: %d )",
                                    $cartSessionStorage->getItemQuantity($productID)
                            ),
           ], 200);
       }


        return new JsonResponse([
            'status' => 'error',
            'message' => $error,
        ], 400);

    }

    #[Route('/shopping-cart-checkout', name: 'app_shopping_cart_checkout')]
    public function chechOut(
        ProductRepository $productRepository,
        CartSessionStorage $cartSessionStorage,
        CartManager $cartManager,
        Request $request
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = new Order();
        $form = $this->createForm(CartCheckoutType::class, $order);
        $cart = $cartSessionStorage->getCart();
        $productIds = is_array($cart) ? array_keys($cart) : [];
        $products = $productRepository->findBy([ 'id' => $productIds]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $cartManager->handleCartSubmit($order);
                $cartSessionStorage->destroyShoppingCart();
                if (count($order->getProductOrders())) {
                    return $this->redirectToRoute('app_shopping_cart_view', ['id' => $order->getId()]);
                } else {
                    return $this->redirectToRoute('app_shopping_cart_checkout');
                }
            } catch (CartManagerException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('shopping_cart/checkout.html.twig', [
            'products' => $products,
            'cart'     => $cart,
            'form'     => $form
        ]);
    }

    #[Route('/shopping-cart-view/{id}', name: 'app_shopping_cart_view')]
    #[IsGranted(CartViewVoter::VIEW, 'order')]
    public function cartView(
        EntityManagerInterface $em,
        Request $request,
        Order $order,
        ProductOrderRepository $productOrderRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(CartCheckoutType::class, $order);
        $productOrders = $productOrderRepository->findByOrder($order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUpdatedAt(new \DateTime());
            $em->persist($order);
            $em->flush();
            $this->addFlash(
                'order-address-update-success',
                "Successfully updated order address."
            );

            return $this->redirectToRoute('app_shopping_cart_view', ['id' => $order->getId()]);
            
        }

        return $this->render('shopping_cart/view.html.twig', [
            'productOrders' => $productOrders,
            'order'         => $order,
            'form'          => $form
        ]); 
    }

    #[Route('/shopping-cart-all-items-delete', name: 'app_shopping_cart_all_items_delete')]
    public function deleteAllItemsInCart( CartSessionStorage $cartSessionStorage): Response
    {
        $cartSessionStorage->destroyShoppingCart();
        return $this->redirectToRoute('app_shopping_cart_checkout');
    }
}
