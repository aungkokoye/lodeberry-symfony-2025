<?php

namespace App\Controller;

use App\Entity\Order;
use App\Order\Form\AdminOrderUpdateType;
use App\Order\Form\AdminOrderViewType;
use App\Repository\OrderRepository;
use App\Repository\ProductOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
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

    #[Route('/admin/order-view', name: 'app_admin_order_view')]
    public function adminOrderView(
        Request $request,
        OrderRepository $orderRepository,
    ): Response {
        $form =  $form = $this->createForm(AdminOrderViewType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uuid = $form->get('uuid')->getData();
            $order = $orderRepository->findOneBy(['uuid' => $uuid]);

            if ($order instanceof Order) {
                 return $this->redirectToRoute('app_admin_order_update', ['id' => $order->getId()]);
            }

            $form->addError(new FormError('Order not found!'));
        }
        return $this->render('order/admin_order_view.html.twig', [
            'form' => $form
         ]);
    }

    #[Route('/admin/order-update/{id}', name: 'app_admin_order_update')]
    public function adminOrderUpdate(
        Request $request,
        OrderRepository $orderRepository,
        ProductOrderRepository $productOrderRepository,
        Order $order,
        EntityManagerInterface $em,
    ): Response {

        $form =  $form = $this->createForm(AdminOrderUpdateType::class, $order);
        $form->handleRequest($request);
        $productOrders = $productOrderRepository->findByOrder($order);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->getUpdatedAt(new \DateTime());
            $em->persist($order);
            $em->flush();

            $this->addFlash(
                'admin-order-update-success',
                "Successfully updated order."
            );

            return $this->redirectToRoute('app_admin_order_update', ['id' => $order->getId()]);
        }

        return $this->render('order/admin_order_update.html.twig', [
            'form'          => $form,
            'order'         => $order,
            'productOrders' => $productOrders
        ]);
    }
}
