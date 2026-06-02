<?php

namespace App\Entity;

use App\Repository\ModificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModificationRepository::class)]
class Modification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'modifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CarModel $model = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EngineVolume $engine_volume = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $production_year = null;

    #[ORM\Column(length: 20)]
    private ?string $drive = null;

    #[ORM\Column(length: 50)]
    private ?string $transmission = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?CarModel
    {
        return $this->model;
    }

    public function setModel(?CarModel $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getEngineVolume(): ?EngineVolume
    {
        return $this->engine_volume;
    }

    public function setEngineVolume(?EngineVolume $engine_volume): static
    {
        $this->engine_volume = $engine_volume;

        return $this;
    }

    public function getProductionYear(): ?\DateTime
    {
        return $this->production_year;
    }

    public function setProductionYear(\DateTime $production_year): static
    {
        $this->production_year = $production_year;

        return $this;
    }

    public function getDrive(): ?string
    {
        return $this->drive;
    }

    public function setDrive(string $drive): static
    {
        $this->drive = $drive;

        return $this;
    }

    public function getTransmission(): ?string
    {
        return $this->transmission;
    }

    public function setTransmission(string $transmission): static
    {
        $this->transmission = $transmission;

        return $this;
    }
}
