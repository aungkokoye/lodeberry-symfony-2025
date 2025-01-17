<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(
        Request $request,
        Security $security,
        OrderRepository $orderRepository,
        PaginatorInterface $paginator,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $loginUser = $security->getUser();

        $query = $orderRepository->findByUser($loginUser, true);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('order/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
