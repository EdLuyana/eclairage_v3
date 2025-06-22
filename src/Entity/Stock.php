<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\UniqueConstraint(name: 'stock_unique', columns: ['product_id', 'location_id', 'size'])]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\Column(length: 20)]
    private ?string $size = null;

    #[ORM\Column]
    private int $quantity = 0;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: StockMovement::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $movements;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(0, $quantity);
        return $this;
    }

    public function incrementQuantity(int $amount): static
    {
        $this->quantity += $amount;
        return $this;
    }

    public function decrementQuantity(int $amount): static
    {
        $this->quantity = max(0, $this->quantity - $amount);
        return $this;
    }

    public function getMovements(): Collection
    {
        return $this->movements;
    }

    public function addMovement(StockMovement $movement): static
    {
        if (!$this->movements->contains($movement)) {
            $this->movements->add($movement);
            $movement->setStock($this);
        }
        return $this;
    }

    public function removeMovement(StockMovement $movement): static
    {
        if ($this->movements->removeElement($movement) && $movement->getStock() === $this) {
            $movement->setStock(null);
        }
        return $this;
    }
}
