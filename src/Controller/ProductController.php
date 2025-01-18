<?php

namespace App\Controller;

use App\Entity\Product;
use App\Order\Form\AdminProductSearchType;
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
            ->andWhere('p.active = :active')
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

    #[Route('/admin/product', name: 'app_admin_product')]
    public function adminList(
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator,
    ): Response {

        $form = $this->createForm(AdminProductSearchType::class);
        $form->handleRequest($request);

        $query = $productRepository->createQueryBuilder('p');

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('search')->getData();
            $active = $form->get('active')->getData();
            
            if (!empty($search)) {
                $query = $query->andWhere(
                    $query->expr()->like('p.name', ':name')
                )
                ->setParameter('name', '%' . $search . '%');
            }

            if ($active === null) {
                $query = $query->andWhere('p.active IN (:active)')
                    ->setParameter('active', [0, 1]);
            } else {
                $query = $query->andWhere('p.active = :active')
                    ->setParameter('active', $active);
            }
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );
        

        return $this->render('product/admin_index.html.twig', [
            'form'       => $form,
            'pagination' => $pagination
        ]);
    }
}
