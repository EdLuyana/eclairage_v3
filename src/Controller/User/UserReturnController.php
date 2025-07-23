<?php

namespace App\Controller\User;

use App\Entity\Product;
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

class UserReturnController extends AbstractController
{
    #[Route('/user/return', name: 'user_return_product')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        StockRepository $stockRepository,
        CurrentLocationService $locationService,
        EntityManagerInterface $em
    ): Response {
        $location = $locationService->getCurrentLocation($request->getSession());

        if ($request->isMethod('POST')) {
            $refInput = $request->request->get('reference');
            $manualSize = $request->request->get('size');

            // Cas 1 : scan = référence-taille
            if ($refInput && str_contains($refInput, '-')) {
                [$reference, $size] = explode('-', $refInput, 2);
            }
            // Cas 2 : saisie manuelle
            elseif ($refInput && $manualSize) {
                $reference = $refInput;
                $size = $manualSize;
            }
            // Cas invalide
            else {
                $this->addFlash('danger', 'Référence ou taille manquante.');
                return $this->redirectToRoute('user_return_product');
            }

            $product = $productRepository->findOneBy(['reference' => $reference]);

            if (!$product) {
                $this->addFlash('danger', 'Produit non trouvé.');
                return $this->redirectToRoute('user_return_product');
            }

            // Mise à jour ou création du stock
            $stock = $stockRepository->findOneBy([
                'product' => $product,
                'size' => $size,
                'location' => $location
            ]);

            if (!$stock) {
                $stock = new Stock();
                $stock->setProduct($product);
                $stock->setSize($size);
                $stock->setLocation($location);
                $stock->setQuantity(0);
                $em->persist($stock);
            }

            $stock->setQuantity($stock->getQuantity() + 1);

            // Création du mouvement
            $movement = new StockMovement();
            $movement->setProduct($product);
            $movement->setSize($size);
            $movement->setLocation($location);
            $movement->setQuantity(1);
            $movement->setUser($this->getUser());
            $movement->setType('RETURN');
            $movement->setPrice($product->getPrice());
            $movement->setStock($stock); // ✅ Lien obligatoire
            $em->persist($movement);

            $em->flush();

            $this->addFlash('success', 'Produit réintégré au stock.');
            return $this->redirectToRoute('user_return_product');
        }

        return $this->render('user/return.html.twig', [
            'location' => $location,
        ]);
    }
}
