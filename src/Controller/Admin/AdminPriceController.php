<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\PriceUpdateForm;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\SeasonRepository;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPriceController extends AbstractController
{
    #[Route('/admin/price/edit', name: 'admin_price_edit')]
    public function edit(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SupplierRepository $supplierRepository,
        SeasonRepository $seasonRepository,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(PriceUpdateForm::class);
        $form->handleRequest($request);

        $updatedCount = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $filterType = $data['filterType'];
            $filterValue = $data['filterValue'];
            $newPrice = $data['newPrice'];

            $products = match ($filterType) {
                'category' => $productRepository->findBy(['category' => $filterValue]),
                'supplier' => $productRepository->findBy(['supplier' => $filterValue]),
                'season'   => $productRepository->findBy(['season' => $filterValue]),
                'reference' => $productRepository->findBy(['reference' => $filterValue]),
                default => [],
            };

            foreach ($products as $product) {
                $product->setPrice($newPrice);
            }

            $em->flush();
            $updatedCount = count($products);

            $this->addFlash('success', "$updatedCount produit(s) mis à jour.");
        }

        return $this->render('admin/price/edit.html.twig', [
            'form' => $form->createView(),
            'updatedCount' => $updatedCount,
        ]);
    }
}
