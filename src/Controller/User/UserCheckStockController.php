<?php

namespace App\Controller\User;

use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserCheckStockController extends AbstractController
{
    #[Route('/user/check-stock', name: 'user_check_stock')]
    public function checkStock(
        Request $request,
        ProductRepository $productRepository,
        StockRepository $stockRepository
    ): Response {
        $reference = $request->query->get('reference');
        $product = null;
        $productSizes = [];
        $stockMap = [];

        if ($reference) {
            $product = $productRepository->findOneBy(['reference' => $reference]);

            if ($product) {
                // Récupère les tailles du produit
                $productSizes = array_map(fn($s) => $s->getValue(), $product->getSizes()->toArray());

                // Mapping par magasin + taille
                $stockMap = $stockRepository->getStockMapByProduct($product);
            }
        }

        return $this->render('user/check_stock.html.twig', [
            'reference' => $reference,
            'product' => $product,
            'sizes' => $productSizes,
            'stockMap' => $stockMap,
        ]);
    }

    #[Route('/user/check-stock/autocomplete', name: 'user_check_stock_autocomplete')]
    public function autocomplete(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $term = $request->query->get('query');

        if (!$term || strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $results = $productRepository->createQueryBuilder('p')
            ->select('p.reference')
            ->where('p.reference LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.reference', 'ASC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_column($results, 'reference'));
    }
}
