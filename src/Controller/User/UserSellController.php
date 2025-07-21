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
        $reference = $request->query->get('reference');
        $product = null;
        $availableSizes = [];

        if ($reference) {
            $product = $productRepository->findOneBy(['reference' => $reference]);
            if ($product) {
                $availableSizes = $stockRepository->findAvailableSizesForProductAndLocation($product, $location);
            }
        }

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

        if ($request->query->get('discount') === '1') {
            $session->set('discount', true);
            return $this->redirectToRoute('user_sell');
        }

        if ($request->query->get('discount') === '0') {
            $session->remove('discount');
            return $this->redirectToRoute('user_sell');
        }

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
                    $stock->decreaseQuantity(1);

                    $movement = new StockMovement();
                    $movement->setProduct($product);
                    $movement->setSize($item['size']);
                    $movement->setLocation($location);
                    $movement->setQuantity(-1);
                    $movement->setUser($this->getUser());
                    $movement->setType('vente');
                    $movement->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($movement);
                }
            }

            $em->flush();
            $session->remove('cart');
            $session->remove('discount');

            $this->addFlash('success', 'Vente enregistrée et panier vidé.');
            return $this->redirectToRoute('user_sell');
        }

        $cart = $session->get('cart', []);
        $discountApplied = $session->get('discount', false);

        return $this->render('user/sell.html.twig', [
            'product' => $product,
            'reference' => $reference,
            'sizes' => $availableSizes,
            'cart' => $cart,
            'discountApplied' => $discountApplied,
        ]);
    }
}
