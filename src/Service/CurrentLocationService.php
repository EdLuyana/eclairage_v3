<?php

namespace App\Service;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentLocationService
{
    private RequestStack $requestStack;
    private LocationRepository $locationRepository;

    public function __construct(RequestStack $requestStack, LocationRepository $locationRepository)
    {
        $this->requestStack = $requestStack;
        $this->locationRepository = $locationRepository;
    }

    public function getCurrentLocation(): ?Location
    {
        $session = $this->requestStack->getSession();
        $locationId = $session->get('current_location_id');

        if (!$locationId) {
            return null;
        }

        return $this->locationRepository->find($locationId);
    }

    public function setCurrentLocation(Location $location): void
    {
        $this->requestStack->getSession()->set('current_location_id', $location->getId());
    }

    public function clearCurrentLocation(): void
    {
        $this->requestStack->getSession()->remove('current_location_id');
    }
}
