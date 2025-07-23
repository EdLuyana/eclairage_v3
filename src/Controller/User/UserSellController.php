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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserSellController extends AbstractController
{
    #[Route('/user/sell', name: 'user_sell')]
    public function sell(
        Request $request,
        ProductRepository $productRepository,
        StockRepository $stockRepository,
        CurrentLocationService $locationService,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $location = $locationService->getCurrentLocation();
        $referenceParam = $request->query->get('reference');
        $product = null;
        $availableSizes = [];

        // 🟢 Détection scan code-barres au format REF-Taille
        if ($referenceParam && str_contains($referenceParam, '-')) {
            [$refScanned, $sizeScanned] = explode('-', $referenceParam, 2);
            $product = $productRepository->findOneBy(['reference' => $refScanned]);

            if ($product) {
                $stock = $stockRepository->findOneBy([
                    'product' => $product,
                    'size' => $sizeScanned,
                    'location' => $location
                ]);

                if ($stock && $stock->getQuantity() > 0) {
                    $cart = $session->get('cart', []);
                    $cart[] = [
                        'reference' => $refScanned,
                        'size' => $sizeScanned,
                        'price' => $product->getPrice(),
                    ];
                    $session->set('cart', $cart);

                    return $this->redirectToRoute('user_sell');
                } else {
                    $this->addFlash('danger', 'Produit ou taille non disponible en stock.');
                    return $this->redirectToRoute('user_sell');
                }
            } else {
                $this->addFlash('danger', 'Référence scannée invalide.');
                return $this->redirectToRoute('user_sell');
            }
        }

        // 🟢 Saisie manuelle via input
        if ($referenceParam && !str_contains($referenceParam, '-')) {
            $product = $productRepository->findOneBy(['reference' => $referenceParam]);
            if ($product) {
                $availableSizes = $stockRepository->findAvailableSizesForProductAndLocation($product, $location);
            } else {
                $this->addFlash('danger', 'Produit introuvable.');
            }
        }

        // 🟢 Ajout manuel au panier (via bouton taille)
        if ($request->isMethod('POST') && $request->request->get('action') === 'add_to_cart') {
            $cart = $session->get('cart', []);
            $cart[] = [
                'reference' => $request->request->get('reference'),
                'size' => $request->request->get('size'),
                'price' => floatval($request->request->get('price')),
            ];
            $session->set('cart', $cart);
            return $this->redirectToRoute('user_sell');
        }

        // 🔄 Appliquer / retirer une réduction
        if ($request->query->get('discount') === '1') {
            $session->set('discount', true);
            return $this->redirectToRoute('user_sell');
        }

        if ($request->query->get('discount') === '0') {
            $session->remove('discount');
            return $this->redirectToRoute('user_sell');
        }

        // 🧹 Vider le panier
        if ($request->query->get('clear') === '1') {
            $session->remove('cart');
            $session->remove('discount');
            return $this->redirectToRoute('user_sell');
        }

        // ✅ Validation finale de la vente
        if ($request->request->get('action') === 'validate_sale') {
            $cart = $session->get('cart', []);
            foreach ($cart as $item) {
                $product = $productRepository->findOneBy(['reference' => $item['reference']]);
                $stock = $stockRepository->findOneBy([
                    'product' => $product,
                    'size' => $item['size'],
                    'location' => $location,
                ]);

                if ($stock && $stock->getQuantity() > 0) {
                    $stock->decrementQuantity(1);

                    $movement = new StockMovement();
                    $movement->setProduct($product);
                    $movement->setSize($item['size']);
                    $movement->setLocation($location);
                    $movement->setStock($stock);
                    $movement->setQuantity(-1);
                    $movement->setUser($this->getUser());
                    $movement->setType('SALE');
                    $movement->setPrice($item['price']);
                    $movement->getCreatedAt(new \DateTimeImmutable());

                    $em->persist($movement);
                }
            }

            $em->flush();
            $session->remove('cart');
            $session->remove('discount');

            $this->addFlash('success', '✅ Vente enregistrée et panier vidé.');
            return $this->redirectToRoute('user_sell');
        }

        // Affichage classique
        $cart = $session->get('cart', []);
        $discountApplied = $session->get('discount', false);

        return $this->render('user/sell.html.twig', [
            'product' => $product,
            'reference' => $referenceParam,
            'sizes' => $availableSizes,
            'cart' => $cart,
            'discountApplied' => $discountApplied,
        ]);
    }

    #[Route('/user/sell/autocomplete', name: 'user_sell_autocomplete')]
    public function autocomplete(
        Request $request,
        StockRepository $stockRepository,
        CurrentLocationService $locationService
    ): Response {
        $term = $request->query->get('query');
        $location = $locationService->getCurrentLocation();

        if (!$term || strlen($term) < 2) {
            return $this->json([]);
        }

        // 🔎 On récupère les références disponibles dans CE magasin
        $results = $stockRepository->findReferencesInLocationByTerm($term, $location);

        return $this->json($results);
    }

}
