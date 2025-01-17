<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\ProductOrder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductOrder>
 */
class ProductOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOrder::class);
    }

       public function findByOrder(Order $order): array
       {
           return $this->createQueryBuilder('po')
                ->innerJoin('po.product', 'p') 
                ->addSelect('p')
                ->andWhere('po.orderRef = :order')
                ->setParameter('order',$order)
                ->getQuery()
                ->getResult()
           ;
       }

       public function findByUser(User $user): array
       {
           return $this->createQueryBuilder('po')
                ->innerJoin('po.product', 'p') 
                ->addSelect('p')
                ->innerJoin('po.orderRef', 'o') 
                ->addSelect('o')
                ->andWhere('o.orderBy = :orderBy')
                ->setParameter('orderBy', $user)
                ->getQuery()
                ->getResult()
           ;
       }

    //    /**
    //     * @return ProductOrder[] Returns an array of ProductOrder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProductOrder
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
