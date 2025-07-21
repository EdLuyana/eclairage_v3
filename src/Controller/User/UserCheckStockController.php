<?php

namespace App\Controller\User;

use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
                foreach ($product->getSizes() as $productSize) {
                    $productSizes[] = $productSize->getValue();
                }

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
}
