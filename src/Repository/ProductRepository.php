<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

    /**
     * @param $fromDate
     * @param $toDate
     * @param $selectFilter
     * @param $searchFilter
     * @return mixed
     */
    public function findTopProductsByDate($fromDate, $toDate, $selectFilter, $searchFilter): mixed
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            if (!empty($selectFilter) && !empty($searchFilter)) {
                if ($selectFilter == "Product") {
                    $filter = " AND p.product_name like '%$searchFilter%' ";
                } elseif ($selectFilter == "Customer") {
                    $filter = " AND (c.first_name like '%$searchFilter%' OR c.last_name like '%$searchFilter%') ";
                } else {
                    $filter = "";
                }
            } else {
                $filter = "";
            }

            $sql =
                "SELECT p.id, p.product_name, p.price, s.quantity, s.total_price, s.payment_method
            FROM product p
            LEFT JOIN sales s ON s.product_id = p.id
            LEFT JOIN customer c on s.customer_id = c.id
            WHERE s.created_at >= :fromDate AND s.created_at <= :toDate
            $filter
            ORDER BY s.quantity DESC
            LIMIT 10";

            $resultSet = $conn->executeQuery($sql, [
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ]);

            // returns an array of Sales objects
            return $resultSet->fetchAllAssociative();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param $selectFilter
     * @param $searchFilter
     * @return mixed
     */
    public function findBottomProductsByDate($fromDate, $toDate, $selectFilter, $searchFilter): mixed
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            if (!empty($selectFilter) && !empty($searchFilter)) {
                if ($selectFilter == "Product") {
                    $filter = " AND p.product_name like '%$searchFilter%' ";
                } elseif ($selectFilter == "Customer") {
                    $filter = " AND (c.first_name like '%$searchFilter%' OR c.last_name like '%$searchFilter%') ";
                } else {
                    $filter = "";
                }
            } else {
                $filter = "";
            }

            $sql =
                "SELECT p.id, p.product_name, p.price, s.quantity, s.total_price, s.payment_method
            FROM product p
            LEFT JOIN sales s ON s.product_id = p.id
            LEFT JOIN customer c on s.customer_id = c.id
            WHERE s.created_at >= :fromDate AND s.created_at <= :toDate
            $filter
            ORDER BY s.quantity ASC
            LIMIT 10";

            $resultSet = $conn->executeQuery($sql, [
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ]);

            // returns an array of Sales objects
            return $resultSet->fetchAllAssociative();
        } catch (\Exception) {
            return null;
        }
    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
