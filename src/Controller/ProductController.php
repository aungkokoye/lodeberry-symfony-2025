<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Storage\CartSessionStorage;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator,
        CartSessionStorage $cartSessionStorage
    ): Response{
        $query = $productRepository->createQueryBuilder('p')
            ->where('p.active = :active')
            ->setParameter('active', true)
        ;

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'cart'       => $cartSessionStorage->getCart()
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_view')]
    public function view(Product $product)
    {
        return $this->render('product/view.html.twig', [
            'product' => $product,
        ]);
    }
}
