<?php

namespace App\Controller\Admin;

use App\Repository\ReassortLineRepository;
use App\Service\LabelGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/labels')]
class AdminLabelController extends AbstractController
{
    #[Route('', name: 'admin_labels_index')]
    public function index(ReassortLineRepository $reassortLineRepository): Response
    {
        // Affichage HTML (facultatif)
        $lines = $reassortLineRepository->findBy(['status' => 'TO_INTEGRATE']);

        $grouped = [];
        foreach ($lines as $line) {
            $locationName = $line->getLocation()->getName();
            $reference = $line->getProduct()->getReference();
            $size = $line->getSize();

            $grouped[$locationName][$reference][$size][] = $line;
        }

        $sizeOrder = ['U', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50', '52'];

        return $this->render('admin/labels/index.html.twig', [
            'groupedLines' => $grouped,
            'sizeOrder' => $sizeOrder
        ]);
    }

    #[Route('/print', name: 'admin_labels_print')]
    public function print(ReassortLineRepository $reassortLineRepository, LabelGeneratorService $labelGenerator): Response
    {
        $lines = $reassortLineRepository->findBy(['status' => 'TO_INTEGRATE']);

        $pdf = $labelGenerator->generateFromReassortLines($lines);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="etiquettes.pdf"',
        ]);
    }
}
