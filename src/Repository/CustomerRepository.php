<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

//    /**
//     * @return Customer[] Returns an array of Customer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
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
    public function findTotalCustomerByDate($fromDate, $toDate, $selectFilter, $searchFilter): mixed
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
                "SELECT count(distinct(id)) as total_customer
                FROM (
                SELECT c.id
                FROM customer c
                LEFT JOIN sales s on c.id = s.customer_id
                LEFT JOIN product p on s.product_id = p.id
                WHERE c.created_at >= :fromDate AND c.created_at <= :toDate
                $filter
                ) a";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue('fromDate', $fromDate);
            $stmt->bindValue('toDate', $toDate);
            $resultSet = $stmt->executeQuery();

            return $resultSet->fetchAssociative();
        } catch (\Exception $exception) {
            return null;
        }
    }

//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
