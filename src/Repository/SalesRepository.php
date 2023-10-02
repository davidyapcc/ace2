<?php

namespace App\Repository;

use App\Entity\Sales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sales>
 *
 * @method Sales|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sales|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sales[]    findAll()
 * @method Sales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sales::class);
    }

    /**
     * @return Sales[] Returns an array of Sales objects
     */
    public function findByDate($date): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.created_at = :date')
            ->setParameter('date', $date)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param $selectFilter
     * @param $searchFilter
     * @return mixed
     */
    public function findTopSalesByDate($fromDate, $toDate, $selectFilter, $searchFilter): mixed
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
            "SELECT s.id, s.quantity, s.total_price, s.payment_method, c.first_name, c.last_name, p.product_name
            FROM sales s
            LEFT JOIN customer c ON s.customer_id = c.id
            LEFT JOIN product p ON s.product_id = p.id
            WHERE s.created_at >= :fromDate AND s.created_at <= :toDate
            $filter
            ORDER BY s.total_price DESC
            LIMIT 10";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue('fromDate', $fromDate);
            $stmt->bindValue('toDate', $toDate);
            $resultSet = $stmt->executeQuery();

//            $resultSet = $conn->executeQuery($sql, [
//                'fromDate' => $fromDate,
//                'toDate' => $toDate,
//            ]);

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
    public function findTotalSalesByDate($fromDate, $toDate, $selectFilter, $searchFilter): mixed
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
                "SELECT count(s.id) as total_sales, sum(s.total_price) as total_revenue
                FROM sales s
                LEFT JOIN customer c on s.customer_id = c.id
                LEFT JOIN product p on s.product_id = p.id
                $filter
                WHERE s.created_at >= :fromDate AND s.created_at <= :toDate";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue('fromDate', $fromDate);
            $stmt->bindValue('toDate', $toDate);
            $resultSet = $stmt->executeQuery();

            return $resultSet->fetchAssociative();
        } catch (\Exception $exception) {
            return null;
        }
    }

//    public function findOneBySomeField($value): ?Sales
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}