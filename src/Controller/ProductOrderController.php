<?php

namespace App\Controller;

use App\Entity\ProductOrder;
use App\Order\Form\AdminProductQuantityUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductOrderController extends AbstractController
{
    #[Route('/admin/product-order-update/{id}', name: 'app_admin_product_order_update')]
    public function update(
        Request $request,
        ProductOrder $productOrder,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(AdminProductQuantityUpdateType::class, $productOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($productOrder);
            $em->flush();

            $this->addFlash(
                'admin-order-update-success',
                "Successfully updated order quantity for {$productOrder->getProduct()->getName()}."
            );

            return $this->redirectToRoute('app_admin_order_update', [
                'id' => $productOrder->getOrderRef()->getId()
            ]);
        }

        return $this->render('product_order/update.html.twig', [
            'form'          => $form,
            'productOrder'  => $productOrder
        ]);
    }
}
