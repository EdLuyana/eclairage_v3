<?php

namespace App\Controller\Admin;

use App\Repository\LocationRepository;
use App\Repository\StockMovementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminActivityController extends AbstractController
{
    #[Route('/admin/activites', name: 'admin_activity_log')]
    public function index(
        Request $request,
        StockMovementRepository $movementRepository,
        LocationRepository $locationRepository,
        UserRepository $userRepository
    ): Response {
        $period = $request->query->get('period', '1w');
        $locationId = $request->query->get('location');
        $userId = $request->query->get('user');

        $movements = $movementRepository->findNonSalesMovementsFiltered($period, $locationId, $userId);
        $locations = $locationRepository->findAll();
        $users = $userRepository->findByRole('ROLE_USER');

        return $this->render('admin/activity/index.html.twig', [
            'movements' => $movements,
            'period' => $period,
            'locationId' => $locationId,
            'userId' => $userId,
            'locations' => $locations,
            'users' => $users,
        ]);
    }
}
