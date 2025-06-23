<?php

namespace App\Controller\Admin;

use App\Repository\ReassortLineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/labels')]
class AdminLabelController extends AbstractController
{
    #[Route('', name: 'admin_labels_index')]
    public function index(ReassortLineRepository $reassortLineRepository): Response
    {
        // On récupère uniquement les lignes de réassort non encore intégrées
        $lines = $reassortLineRepository->findBy(['status' => 'TO_INTEGRATE']);

        // Tri par magasin > référence > taille
        $grouped = [];

        foreach ($lines as $line) {
            $locationName = $line->getLocation()->getName();
            $reference = $line->getProduct()->getReference();
            $size = $line->getSize();

            $grouped[$locationName][$reference][$size][] = $line;
        }

        // Tri des tailles pour affichage ordonné
        $sizeOrder = ['XS', 'S', 'M', 'L', 'XL', '36', '38', '40', '42', '44'];

        return $this->render('admin/labels/index.html.twig', [
            'groupedLines' => $grouped,
            'sizeOrder' => $sizeOrder
        ]);
    }
}
