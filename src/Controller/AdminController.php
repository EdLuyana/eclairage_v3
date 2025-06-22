<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Entity\Season;
use App\Entity\Category;
use App\Entity\Product;
use App\Form\SupplierForm;
use App\Form\SeasonForm;
use App\Form\CategoryForm;
use App\Form\ProductForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/test/supplier', name: 'admin_test_supplier')]
    public function testSupplierForm(Request $request, EntityManagerInterface $em): Response
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierForm::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supplier);
            $em->flush();
            $this->addFlash('success', 'Fournisseur ajouté avec succès.');
            return $this->redirectToRoute('admin_test_supplier');
        }

        return $this->render('admin/test_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/test/season', name: 'admin_test_season')]
    public function testSeasonForm(Request $request, EntityManagerInterface $em): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonForm::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($season);
            $em->flush();
            $this->addFlash('success', 'Collection ajoutée avec succès.');
            return $this->redirectToRoute('admin_test_season');
        }

        return $this->render('admin/test_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/test/category', name: 'admin_test_category')]
    public function testCategoryForm(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie ajoutée avec succès.');
            return $this->redirectToRoute('admin_test_category');
        }

        return $this->render('admin/test_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/test/product', name: 'admin_test_product')]
    public function testProductForm(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('admin_test_product');
        }

        return $this->render('admin/test_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
