<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Sales;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $today = date('Y-m-d');
        $selectedFromDate = $request->query->get('selectedFromDate', $today);
        $selectedToDate = $request->query->get('selectedToDate', $today);
        $selectFilter = $request->query->get('selectFilter', '');
        $searchFilter = $request->query->get('searchFilter', '');

        if (!empty($selectedFromDate) && !empty($selectedToDate)) {
            $fromDate = $selectedFromDate . " 00:00:00";
            $toDate = $selectedToDate . " 23:59:59";
        } else {
            $fromDate = date('Y-m-d 00:00:00');
            $toDate = date('Y-m-d 23:59:59');
        }

        if (empty($selectFilter)) {
            $searchFilter = "";
        }
        if (empty($searchFilter)) {
                $selectFilter = "";
        }

        $topSalesList = $entityManager->getRepository(Sales::class)->findTopSalesByDate($fromDate, $toDate, $selectFilter, $searchFilter);
        $topProductsList = $entityManager->getRepository(Product::class)->findTopProductsByDate($fromDate, $toDate, $selectFilter, $searchFilter);
        $bottomProductsList = $entityManager->getRepository(Product::class)->findBottomProductsByDate($fromDate, $toDate, $selectFilter, $searchFilter);

        $totalSales = $entityManager->getRepository(Sales::class)->findTotalSalesByDate($fromDate, $toDate, $selectFilter, $searchFilter);
        $totalCustomer = $entityManager->getRepository(Customer::class)->findTotalCustomerByDate($fromDate, $toDate, $selectFilter, $searchFilter);

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'topSalesList' => $topSalesList,
            'topProductsList' => $topProductsList,
            'bottomProductsList' => $bottomProductsList,
            'total_sales' => $totalSales,
            'total_customer' => $totalCustomer,
            'today'=> $today,
            'selectedFromDate' => $selectedFromDate,
            'selectedToDate' => $selectedToDate,
            'selectFilter' => $selectFilter,
            'searchFilter' => $searchFilter
        ]);
    }
}
