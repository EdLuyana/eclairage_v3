<?php

namespace App\Service;

use App\Entity\Location;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentLocationService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getCurrentLocation(): ?Location
    {
        $session = $this->requestStack->getSession();
        return $session->get('current_location');
    }

    public function setCurrentLocation(Location $location): void
    {
        $this->requestStack->getSession()->set('current_location', $location);
    }

    public function clearCurrentLocation(): void
    {
        $this->requestStack->getSession()->remove('current_location');
    }
}
