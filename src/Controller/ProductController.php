<?php

namespace App\Controller;

use App\Entity\Product;
use App\Product\Form\AdminProductSearchType;
use App\Product\Form\ProductSearchType;
use App\Product\Form\ProductType;
use App\Repository\ProductRepository;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator,
        CartSessionStorage $cartSessionStorage
    ): Response{
        $form = $this->createForm(ProductSearchType::class);
        $form->handleRequest($request);
        $query = $productRepository->createQueryBuilder('p')
            ->andWhere('p.active = :active')
            ->setParameter('active', true)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('search')->getData();
            if (!empty($search)) {
                $query = $query->andWhere(
                    $query->expr()->like('p.name', ':name')
                )
                ->setParameter('name', '%' . $search . '%');
            }
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'cart'       => $cartSessionStorage->getCart(),
            'form'       => $form
        ]);
    }

    #[Route('/product/view/{id}', name: 'app_product_view')]
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

    #[Route('/admin/product/create', name: 'app_admin_product_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%')] string $directory
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('imageFile')->getData();
            $uuid = Uuid::uuid4()->toString();
            if ($file) {
                $newFilename = "{$uuid}.{$file->guessExtension()}";
                try {
                    $imageFileDirectory = $directory. "/assets/images/product/";
                    $file->move($imageFileDirectory, $newFilename);
                    $product->setUuid($uuid)
                            ->setImage($newFilename);
                    $em->persist($product);
                    $em->flush();
                    $this->runNpmCommand($directory);
                    return $this->redirectToRoute('app_admin_product');
                } catch (FileException $e) {
                    $this->addFlash(
                        'admin-product-create-error',
                        "Fail to upload the file.",
                    );
                }
            } else {
                $form->addError(new FormError('You need to upload the product image file!'));
            }
        }

        return $this->render('product/admin_product_create.html.twig', [
           'form' => $form
        ]);
    }

    #[Route('/admin/product/update/{id}', name: 'app_admin_product_update')]
    public function update(
        Request $request,
        Product $product,
        EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%')] string $directory
    ): Response {
        $imageFileDirectory = $directory. "/assets/images/product/";
        $oldFilePath= $imageFileDirectory . $product->getImage();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('imageFile')->getData();
        
            if ($file) {
                try {
                    $uuid = Uuid::uuid4()->toString();
                    $newFilename = "{$uuid}.{$file->guessExtension()}";
                    $file->move($imageFileDirectory, $newFilename);
                    $product->setUuid($uuid)->setImage($newFilename);

                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                    $this->runNpmCommand($directory); 
                } catch (FileException $e) {
                    $this->addFlash(
                        'admin-product-create-error',
                        "Fail to upload the file.",
                    );

                    return $this->render('product/admin_product_update.html.twig', [
                        'form'      => $form,
                        'product'   => $product
                     ]);
                }
            }

            $em->persist($product);
            $em->flush(); 
            return $this->redirectToRoute('app_admin_product'); 
        }

        return $this->render('product/admin_product_update.html.twig', [
            'form'      => $form,
            'product'   => $product
         ]);
    }

    private function runNpmCommand(string $directory): void
    {
        $command = 'npm run dev';
        shell_exec("cd $directory && $command 2>&1");
    }
}
