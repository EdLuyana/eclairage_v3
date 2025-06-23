<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/product')]
class AdminProductController extends AbstractController
{
    #[Route('/create', name: 'admin_product_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        // Référence auto à ajouter ici plus tard

        if ($form->isSubmitted() && $form->isValid()) {
            // La logique de génération de la référence produit sera ici plus tard
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('admin_product_create');
        }

        return $this->render('admin/product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
