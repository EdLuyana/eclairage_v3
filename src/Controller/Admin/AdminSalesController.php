<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Location;
use App\Repository\StockMovementRepository;
use App\Repository\UserRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/sales')]
class AdminSalesController extends AbstractController
{
    #[Route('', name: 'admin_sales_index')]
    public function index(
        Request $request,
        StockMovementRepository $movementRepository,
        UserRepository $userRepository,
        LocationRepository $locationRepository
    ): Response {
        // Périodes prédéfinies
        $period = $request->query->get('period', '30d');
        $now = new \DateTimeImmutable('now');

        switch ($period) {
            case '24h':
                $start = $now->modify('-1 day');
                break;
            case '1w':
                $start = $now->modify('-7 days');
                break;
            case '1m':
                $start = $now->modify('-1 month');
                break;
            case 'month':
                $start = $now->modify('first day of this month')->setTime(0, 0);
                break;
            case 'year':
                $start = $now->modify('first day of January')->setTime(0, 0);
                break;
            case 'all':
                $start = (new \DateTimeImmutable('2000-01-01'))->setTime(0, 0);
                break;
            default:
                $start = $now->modify('-30 days');
        }

        $end = $now->setTime(23, 59, 59);

        // Filtres optionnels
        $userId = $request->query->get('user');
        $locationId = $request->query->get('location');

        $qb = $movementRepository->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.createdAt BETWEEN :start AND :end')
            ->setParameter('type', 'SALE')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('m.createdAt', 'DESC');

        if ($userId) {
            $qb->andWhere('m.user = :user')->setParameter('user', $userId);
        }

        if ($locationId) {
            $qb->andWhere('m.location = :location')->setParameter('location', $locationId);
        }

        $movements = $qb->getQuery()->getResult();

        return $this->render('admin/sales/index.html.twig', [
            'movements' => $movements,
            'period' => $period,
            'userId' => $userId,
            'locationId' => $locationId,
            'users' => $userRepository->findByRole('ROLE_USER'),
            'locations' => $locationRepository->findAll(),
        ]);
    }
}
