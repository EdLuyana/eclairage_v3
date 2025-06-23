<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Supplier;
use App\Form\CategoryForm;
use App\Form\SeasonForm;
use App\Form\SupplierForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/data')]
class AdminDataController extends AbstractController
{
    #[Route('/{type}', name: 'admin_data_manage')]
    public function manage(string $type, Request $request, EntityManagerInterface $em): Response
    {
        $map = [
            'supplier' => [Supplier::class, SupplierForm::class, 'Fournisseur'],
            'category' => [Category::class, CategoryForm::class, 'Catégorie'],
            'season'   => [Season::class, SeasonForm::class, 'Collection'],
        ];

        if (!isset($map[$type])) {
            throw $this->createNotFoundException('Type non reconnu.');
        }

        [$entityClass, $formClass, $label] = $map[$type];
        $entity = new $entityClass();
        /** @var FormInterface $form */
        $form = $this->createForm($formClass, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', "$label ajouté avec succès.");
            return $this->redirectToRoute('admin_data_manage', ['type' => $type]);
        }

        return $this->render('admin/data/form.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'label' => $label
        ]);
    }
}
