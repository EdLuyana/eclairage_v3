<?php

namespace App\Controller\Admin;

use App\Repository\LocationRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/stocks')]
class AdminStockController extends AbstractController
{
    #[Route('/', name: 'admin_stock_index')]
    public function index(
        Request $request,
        StockRepository $stockRepository,
        StockMovementRepository $movementRepository,
        LocationRepository $locationRepository
    ): Response {
        $locationId = $request->query->get('location');
        $locations = $locationRepository->findAll();

        // Filtrage éventuel par magasin
        $stocks = $locationId
            ? $stockRepository->findByLocationWithProductAndSize($locationId)
            : $stockRepository->findAllWithProductAndSize();

        // Préparer un tableau pour compter les ventes sur 30 jours
        $sales = $movementRepository->countSalesLast30DaysIndexed();

        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $stocks,
            'sales' => $sales,
            'locations' => $locations,
            'locationId' => $locationId,
        ]);
    }
}
