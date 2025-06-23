<?php

namespace App\Controller\Admin;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/export')]
class AdminExportController extends AbstractController
{
    #[Route('/sales/pdf', name: 'admin_export_sales_pdf')]
    public function exportSalesPdf(Request $request, StockMovementRepository $movementRepository): Response
    {
        $period = $request->query->get('period', '30d');
        $userId = $request->query->get('user');
        $locationId = $request->query->get('location');

        $now = new \DateTimeImmutable('now');
        switch ($period) {
            case '24h': $start = $now->modify('-1 day'); break;
            case '1w': $start = $now->modify('-7 days'); break;
            case '1m': $start = $now->modify('-1 month'); break;
            case 'month': $start = $now->modify('first day of this month'); break;
            case 'year': $start = $now->modify('first day of January'); break;
            case 'all': $start = new \DateTimeImmutable('2000-01-01'); break;
            default: $start = $now->modify('-30 days');
        }
        $end = $now->setTime(23, 59, 59);

        $qb = $movementRepository->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.createdAt BETWEEN :start AND :end')
            ->setParameter('type', 'SALE')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($userId) {
            $qb->andWhere('m.user = :user')->setParameter('user', $userId);
        }

        if ($locationId) {
            $qb->andWhere('m.location = :location')->setParameter('location', $locationId);
        }

        $movements = $qb->getQuery()->getResult();

        $html = $this->renderView('admin/sales/pdf.html.twig', [
            'movements' => $movements,
            'start' => $start,
            'end' => $end,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ventes.pdf"',
        ]);
    }
}
