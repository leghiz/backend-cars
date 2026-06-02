<?php

namespace App\Entity;

use App\Repository\BackgroundRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackgroundRepository::class)]
class Background
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'background', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lot $lot = null;

    #[ORM\Column]
    private ?int $mileage = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_repainted = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(Lot $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function isRepainted(): ?bool
    {
        return $this->is_repainted;
    }

    public function setIsRepainted(?bool $is_repainted): static
    {
        $this->is_repainted = $is_repainted;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }
}
