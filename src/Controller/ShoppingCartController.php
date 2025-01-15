<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductOrder;
use App\Repository\ProductRepository;
use App\Storage\CartSessionStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShoppingCartController extends AbstractController
{
    #[Route('/shopping-cart', name: 'app_shopping_cart')]
    public function index(Request $request, CartSessionStorage $cartSessionStorage): Response
    {
        $cartSessionStorage->destroyShoppingCart();
        //dd($cartSessionStorage->getCart());
        return $this->render('shopping_cart/index.html.twig', [
            'controller_name' => 'ShoppingCartController',
        ]);
    }

    #[Route('/shopping-cart-add', name: 'app_shopping_cart_add', methods: ['POST'])]
    public function addItem(
        Request $request,
        ProductRepository $productRepository,
        CartSessionStorage $cartSessionStorage
    ):JsonResponse
    {
        $error = null;
        $data = json_decode($request->getContent(), true);
        $productID = (int) $data['id'];

        $product = $productRepository->find($productID);

        if ($product instanceof Product) {
            $quantity = $cartSessionStorage->getItemQuantity($productID);

            if ($quantity >= ProductOrder::MAX_QUANTITY) {
                $error = sprintf("Reach max quantity.  (Total: %d )",  ProductOrder::MAX_QUANTITY);
            }

        } else {
            $error = 'Product not found!';
        }

       if ($error === null) {
           $cartSessionStorage->setItem($productID, 1);

           return new JsonResponse([
               'status'     => 'success',
               'message'    => sprintf(
                            "Successfully added to shopping cart. (Total: %d )",
                                    $cartSessionStorage->getItemQuantity($productID)
                            ),
           ], 200);
       }


        return new JsonResponse([
            'status' => 'error',
            'message' => $error,
        ], 400);

    }
}
