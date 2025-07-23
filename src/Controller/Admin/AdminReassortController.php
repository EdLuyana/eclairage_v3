<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ReassortLine;
use App\Repository\ProductRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/reassort')]
class AdminReassortController extends AbstractController
{
    #[Route('', name: 'admin_reassort_start')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $reference = $request->query->get('reference');
        $product = null;

        if ($reference) {
            $product = $productRepository->findOneBy(['reference' => $reference]);

            if (!$product) {
                $this->addFlash('danger', "Aucun produit trouvé pour la référence « $reference ».");
            }
        }

        return $this->render('admin/reassort/index.html.twig', [
            'product' => $product,
            'reference' => $reference,
            'productSizes' => $product ? $product->getSizes() : [],
        ]);
    }

    #[Route('/distribute', name: 'admin_reassort_distribute', methods: ['POST'])]
    public function distribute(Request $request, ProductRepository $productRepository, LocationRepository $locationRepository): Response
    {
        $sizesInput = $request->request->all('sizes');

        if (empty($sizesInput)) {
            $this->addFlash('danger', "Aucun produit à répartir.");
            return $this->redirectToRoute('admin_reassort_batch');
        }

        $products = [];
        foreach ($sizesInput as $reference => $sizes) {
            $product = $productRepository->findOneBy(['reference' => $reference]);

            if ($product && is_array($sizes)) {
                $filteredSizes = array_filter($sizes, fn($qty) => (int)$qty > 0);
                if (!empty($filteredSizes)) {
                    $products[] = [
                        'product' => $product,
                        'sizes' => $filteredSizes,
                    ];
                }
            }
        }

        if (empty($products)) {
            $this->addFlash('danger', "Aucun produit valide à répartir.");
            return $this->redirectToRoute('admin_reassort_batch');
        }

        return $this->render('admin/reassort/distribute.html.twig', [
            'products' => $products,
            'locations' => $locationRepository->findAll(),
        ]);
    }

    #[Route('/review', name: 'admin_reassort_review', methods: ['POST'])]
    public function review(Request $request, ProductRepository $productRepository, LocationRepository $locationRepository): Response
    {
        $distributions = $request->request->all('distributions');
        $sizes = $request->request->all('sizes');

        if (empty($distributions) || empty($sizes)) {
            $this->addFlash('danger', "Données manquantes.");
            return $this->redirectToRoute('admin_reassort_batch');
        }

        $products = [];
        foreach ($sizes as $reference => $sizeList) {
            $product = $productRepository->findOneBy(['reference' => $reference]);
            if (!$product) continue;

            $products[] = $product;
        }

        $locations = [];
        foreach ($distributions as $ref => $sizeMap) {
            foreach ($sizeMap as $size => $locs) {
                foreach ($locs as $locationId => $qty) {
                    if (!isset($locations[$locationId])) {
                        $locations[$locationId] = $locationRepository->find($locationId);
                    }
                }
            }
        }

        return $this->render('admin/reassort/confirm.html.twig', [
            'products' => $products,
            'distributions' => $distributions,
            'locations' => $locations,
        ]);
    }

    #[Route('/confirm', name: 'admin_reassort_confirm', methods: ['POST'])]
    public function confirm(Request $request, ProductRepository $productRepository, LocationRepository $locationRepository, EntityManagerInterface $em): Response
    {
        $distributions = $request->request->all('distributions');

        if (empty($distributions)) {
            $this->addFlash('danger', "Données incomplètes.");
            return $this->redirectToRoute('admin_reassort_batch');
        }

        foreach ($distributions as $ref => $sizeMap) {
            $product = $productRepository->findOneBy(['reference' => $ref]);
            if (!$product) continue;

            foreach ($sizeMap as $size => $locations) {
                foreach ($locations as $locationId => $qty) {
                    $qty = (int)$qty;
                    if ($qty > 0) {
                        $location = $locationRepository->find($locationId);
                        if (!$location) continue;

                        for ($i = 0; $i < $qty; $i++) {
                            $line = new ReassortLine();
                            $line->setProduct($product);
                            $line->setSize($size);
                            $line->setLocation($location);
                            $line->setQuantity(1);
                            $line->setStatus('TO_INTEGRATE');
                            $line->setCreatedAt(new \DateTimeImmutable());

                            $em->persist($line);
                        }
                    }
                }
            }
        }

        $em->flush();

        $this->addFlash('success', 'Réassort enregistré avec succès.');
        return $this->redirectToRoute('admin_reassort_batch');
    }

    #[Route('/autocomplete', name: 'admin_reassort_autocomplete', methods: ['GET'])]
    public function autocomplete(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $term = $request->query->get('query', '');

        $results = $productRepository->createQueryBuilder('p')
            ->select('p.reference')
            ->where('p.reference LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->json(array_column($results, 'reference'));
    }

    #[Route('/batch', name: 'admin_reassort_batch', methods: ['GET'])]
    public function batch(): Response
    {
        return $this->render('admin/reassort/batch.html.twig');
    }

    #[Route('/get-sizes', name: 'admin_reassort_get_sizes', methods: ['GET'])]
    public function getSizes(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $reference = $request->query->get('reference');
        $product = $productRepository->findOneBy(['reference' => $reference]);

        if (!$product) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $sizes = $product->getSizes();

        $response = [
            'reference' => $product->getReference(),
            'name' => $product->getName(),
            'sizes' => [
                'US' => [],
                'FR' => [],
                'UNIQUE' => [],
            ]
        ];

        foreach ($sizes as $size) {
            $value = strtoupper($size->getValue());

            if (in_array($value, ['S', 'M', 'L', 'XL', 'XXL'])) {
                $response['sizes']['US'][] = $value;
            } elseif ($value === 'U') {
                $response['sizes']['UNIQUE'][] = $value;
            } elseif (ctype_digit($value) && (int)$value >= 32 && (int)$value <= 52) {
                $response['sizes']['FR'][] = $value;
            }
        }

        return $this->json($response);
    }
}
