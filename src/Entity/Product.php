<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column(length: 30)]
    private string $color;

    #[ORM\Column(length: 60, unique: true)]
    private string $reference = '';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(length: 20)]
    private string $status = 'to_integrate';

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Season $season = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Stock::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $stocks;

    #[ORM\ManyToMany(targetEntity: Size::class)]
    private Collection $sizes;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ReassortLine::class, orphanRemoval: true)]
    private Collection $reassortLines;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->sizes = new ArrayCollection();
        $this->reassortLines = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getColor(): string { return $this->color; }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getReference(): string { return $this->reference; }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getPrice(): float { return $this->price; }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getStatus(): string { return $this->status; }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSupplier(): ?Supplier { return $this->supplier; }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getSeason(): ?Season { return $this->season; }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getCategory(): ?Category { return $this->category; }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getStocks(): Collection { return $this->stocks; }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProduct($this);
        }
        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock) && $stock->getProduct() === $this) {
            $stock->setProduct(null);
        }
        return $this;
    }

    public function getSizes(): Collection { return $this->sizes; }

    public function addSize(Size $size): static
    {
        if (!$this->sizes->contains($size)) {
            $this->sizes->add($size);
        }
        return $this;
    }

    public function removeSize(Size $size): static
    {
        $this->sizes->removeElement($size);
        return $this;
    }

    public function getReassortLines(): Collection { return $this->reassortLines; }

    public function addReassortLine(ReassortLine $line): static
    {
        if (!$this->reassortLines->contains($line)) {
            $this->reassortLines->add($line);
            $line->setProduct($this);
        }
        return $this;
    }

    public function removeReassortLine(ReassortLine $line): static
    {
        if ($this->reassortLines->removeElement($line) && $line->getProduct() === $this) {
            $line->setProduct(null);
        }
        return $this;
    }
}
