<?php

namespace App\Controller\User;

use App\Entity\Stock;
use App\Entity\StockMovement;
use App\Repository\ProductRepository;
use App\Repository\ReassortLineRepository;
use App\Repository\StockRepository;
use App\Repository\LocationRepository;
use App\Service\CurrentLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAddStockController extends AbstractController
{
    #[Route('/user/add-stock', name: 'user_add_stock')]
    public function addStock(
        Request $request,
        ProductRepository $productRepository,
        ReassortLineRepository $reassortLineRepository,
        StockRepository $stockRepository,
        LocationRepository $locationRepository,
        CurrentLocationService $locationService,
        EntityManagerInterface $em
    ): Response {
        $reference = $request->query->get('reference');
        $product = null;
        $sizes = [];

        $locationId = $locationService->getCurrentLocation()?->getId();
        $location = $locationId ? $locationRepository->find($locationId) : null;

        if (!$location) {
            throw $this->createNotFoundException("Aucun magasin sélectionné.");
        }

        if ($reference) {
            $product = $productRepository->findOneBy(['reference' => $reference]);

            if ($product) {
                $lines = $reassortLineRepository->findBy([
                    'product' => $product,
                    'location' => $location,
                    'status' => 'TO_INTEGRATE',
                ]);

                // 🔁 Comptage des quantités par taille
                $sizes = [];
                foreach ($lines as $line) {
                    for ($i = 0; $i < $line->getQuantity(); $i++) {
                        $size = $line->getSize();
                        $sizes[$size] = ($sizes[$size] ?? 0) + 1;
                    }
                }
            }
        }

        // 📦 Intégration réelle d'un produit (après clic pastille)
        if ($request->isMethod('POST')) {
            $reference = $request->request->get('reference');
            $size = $request->request->get('size');

            if (!$reference || !$size || !$location) {
                return new JsonResponse(['error' => 'Paramètres manquants.'], 400);
            }

            $product = $productRepository->findOneBy(['reference' => $reference]);

            if (!$product) {
                return new JsonResponse(['error' => 'Produit introuvable.'], 404);
            }

            $stock = $stockRepository->findOneBy([
                'product' => $product,
                'size' => $size,
                'location' => $location,
            ]);

            if (!$stock) {
                $stock = new \App\Entity\Stock();
                $stock->setProduct($product);
                $stock->setSize($size);
                $stock->setLocation($location);
                $stock->setQuantity(0);
                $em->persist($stock);
            }

            $stock->incrementQuantity(1);

            // 🔁 Récupère toutes les lignes TO_INTEGRATE restantes pour cette référence/taille/magasin
            $availableLines = $reassortLineRepository->findBy([
                'product' => $product,
                'size' => $size,
                'location' => $location,
                'status' => 'TO_INTEGRATE',
            ], ['id' => 'ASC']);

// ✅ On intègre une seule ligne (celle avec l’ID le plus bas)
            if (!empty($availableLines)) {
                $lineToIntegrate = $availableLines[0];
                $lineToIntegrate->setStatus('INTEGRATED');
                $em->persist($lineToIntegrate);
            }

            $movement = new StockMovement();
            $movement->setProduct($product);
            $movement->setSize($size);
            $movement->setLocation($location);
            $movement->setStock($stock);
            $movement->setQuantity(1);
            $movement->setUser($this->getUser());
            $movement->setType('ajout');
            $movement->getCreatedAt(new \DateTimeImmutable());

            $em->persist($movement);
            $em->flush();

            return new JsonResponse(['success' => true]);
        }

        return $this->render('user/add_stock.html.twig', [
            'reference' => $reference,
            'product' => $product,
            'sizes' => $sizes,
        ]);
    }

    #[Route('/user/add-stock/autocomplete', name: 'user_add_stock_autocomplete')]
    public function autocomplete(
        Request $request,
        ReassortLineRepository $reassortLineRepository,
        LocationRepository $locationRepository,
        CurrentLocationService $locationService
    ): JsonResponse {
        $query = $request->query->get('query', '');

        $locationId = $locationService->getCurrentLocation()?->getId();
        $location = $locationId ? $locationRepository->find($locationId) : null;

        if (!$location) {
            return $this->json([], 400);
        }

        $results = $reassortLineRepository->findDistinctReferencesToIntegrate($location, $query);

        return $this->json($results);
    }
}
