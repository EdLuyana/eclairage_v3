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
        ]);
    }

    #[Route('/distribute', name: 'admin_reassort_distribute', methods: ['POST'])]
    public function distribute(Request $request, ProductRepository $productRepository, LocationRepository $locationRepository, EntityManagerInterface $em): Response
    {
        $reference = $request->request->get('reference');
        $sizes = $request->request->all('sizes');
        $product = $productRepository->findOneBy(['reference' => $reference]);

        if (!$product || empty($sizes)) {
            $this->addFlash('danger', "Référence ou tailles invalides.");
            return $this->redirectToRoute('admin_reassort_start');
        }

        $locations = $locationRepository->findAll();

        return $this->render('admin/reassort/distribute.html.twig', [
            'product' => $product,
            'sizes' => $sizes,
            'locations' => $locations,
        ]);
    }

    #[Route('/confirm', name: 'admin_reassort_confirm', methods: ['POST'])]
    public function confirm(Request $request, ProductRepository $productRepository, LocationRepository $locationRepository, EntityManagerInterface $em): Response
    {
        $reference = $request->request->get('reference');
        $distributions = $request->request->all('distributions');
        $product = $productRepository->findOneBy(['reference' => $reference]);

        if (!$product || empty($distributions)) {
            $this->addFlash('danger', "Données incomplètes.");
            return $this->redirectToRoute('admin_reassort_start');
        }

        foreach ($distributions as $size => $locations) {
            foreach ($locations as $locationId => $qty) {
                $qty = (int) $qty;
                if ($qty > 0) {
                    $location = $locationRepository->find($locationId);
                    $line = new ReassortLine();
                    $line->setProduct($product);
                    $line->setLocation($location);
                    $line->setSize($size);
                    $line->setQuantity($qty);
                    $line->setStatus('TO_INTEGRATE');
                    $line->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($line);
                }
            }
        }

        $em->flush();

        $this->addFlash('success', 'Réassort enregistré avec succès.');
        return $this->redirectToRoute('admin_reassort_start');
    }
}
