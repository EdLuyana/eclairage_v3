<?php

namespace App\Controller\User;

use App\Entity\Stock;
use App\Entity\StockMovement;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Service\CurrentLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAddStockController extends AbstractController
{
    #[Route('/user/add-stock', name: 'user_add_stock')]
    public function addStock(
        Request $request,
        ProductRepository $productRepository,
        StockRepository $stockRepository,
        CurrentLocationService $locationService,
        EntityManagerInterface $em
    ): Response {
        $reference = $request->query->get('reference');
        $product = null;
        $sizes = [];
        $location = $locationService->getCurrentLocation();

        if ($reference) {
            $product = $productRepository->findOneBy(['reference' => $reference]);

            if ($product) {
                foreach ($product->getSizes() as $productSize) {
                    $sizes[] = $productSize->getValue();
                }
            }
        }

        if ($request->isMethod('POST')) {
            $reference = $request->request->get('reference');
            $size = $request->request->get('size');

            $product = $productRepository->findOneBy(['reference' => $reference]);

            if ($product && $size && $location) {
                $stock = $stockRepository->findOneBy([
                    'product' => $product,
                    'size' => $size,
                    'location' => $location,
                ]);

                if (!$stock) {
                    $stock = new Stock();
                    $stock->setProduct($product);
                    $stock->setSize($size);
                    $stock->setLocation($location);
                    $stock->setQuantity(0);
                    $em->persist($stock);
                }

                $stock->increaseQuantity(1);

                $movement = new StockMovement();
                $movement->setProduct($product);
                $movement->setSize($size);
                $movement->setLocation($location);
                $movement->setQuantity(1);
                $movement->setUser($this->getUser());
                $movement->setType('ajout');
                $movement->setCreatedAt(new \DateTimeImmutable());

                $em->persist($movement);
                $em->flush();

                $this->addFlash('success', "Produit ajouté au stock ({$product->getName()} - taille {$size})");

                return $this->redirectToRoute('user_add_stock');
            }
        }

        return $this->render('user/add_stock.html.twig', [
            'reference' => $reference,
            'product' => $product,
            'sizes' => $sizes,
        ]);
    }
}
