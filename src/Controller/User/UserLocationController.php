<?php

namespace App\Controller\User;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\CurrentLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserLocationController extends AbstractController
{
    #[Route('/user/select-location', name: 'user_select_location')]
    public function selectLocation(
        Request $request,
        LocationRepository $locationRepository,
        CurrentLocationService $locationService
    ): Response {
        $locations = $locationRepository->findAll();

        if ($request->isMethod('POST')) {
            $locationId = $request->request->get('location');
            $location = $locationRepository->find($locationId);

            if ($location) {
                $locationService->setCurrentLocation($location);
                return $this->redirectToRoute('user_dashboard');
            }

            $this->addFlash('danger', 'Magasin invalide.');
        }

        return $this->render('user/select_location.html.twig', [
            'locations' => $locations,
        ]);
    }
    #[Route('/user/change-location', name: 'user_change_location')]
    public function changeLocation(CurrentLocationService $locationService): Response
    {
        $locationService->clearCurrentLocation();
        return $this->redirectToRoute('user_select_location');
    }
}
