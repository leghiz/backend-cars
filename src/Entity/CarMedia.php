<?php

namespace App\Entity;

use App\Repository\CarMediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarMediaRepository::class)]
class CarMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carMedia')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lot $lot = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $file_path = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }
}
