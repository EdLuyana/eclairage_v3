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

        $grouped = [];

        foreach ($stocks as $stock) {
            $reference = $stock->getProduct()->getReference();
            $locationName = $stock->getLocation()->getName();
            $key = $reference . '_' . $locationName;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'reference' => $reference,
                    'location' => $locationName, // 👈 ici on garde une chaîne simple
                    'sizes' => [],
                    'sales' => $sales[$reference][$locationName] ?? 0,
                ];
            }

            $grouped[$key]['sizes'][] = [
                'size' => $stock->getSize(),
                'quantity' => $stock->getQuantity(),
            ];
        }

//        return $this->render('admin/stock/index.html.twig', [
//            'stocks' => $stocks,
//            'locations' => $locationRepository->findAll(),
//            'selectedLocationId' => $selectedLocationId,
//            'grouped' => $grouped,
//        ]);

        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $stocks,
            'sales' => $sales,
            'locations' => $locations,
            'locationId' => $locationId,
            'grouped' => $grouped,
         ]);
    }
}
