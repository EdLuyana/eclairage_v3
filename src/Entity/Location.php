<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Stock::class, orphanRemoval: true)]
    private Collection $stocks;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: ReassortLine::class, orphanRemoval: true)]
    private Collection $reassortLines;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->reassortLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setLocation($this);
        }
        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock) && $stock->getLocation() === $this) {
            $stock->setLocation(null);
        }
        return $this;
    }

    public function getReassortLines(): Collection
    {
        return $this->reassortLines;
    }

    public function addReassortLine(ReassortLine $line): static
    {
        if (!$this->reassortLines->contains($line)) {
            $this->reassortLines->add($line);
            $line->setLocation($this);
        }
        return $this;
    }

    public function removeReassortLine(ReassortLine $line): static
    {
        if ($this->reassortLines->removeElement($line) && $line->getLocation() === $this) {
            $line->setLocation(null);
        }
        return $this;
    }
}
