<?php

namespace App\Entity;

use App\Repository\EngineVolumeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EngineVolumeRepository::class)]
class EngineVolume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $volume = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(string $volume): static
    {
        $this->volume = $volume;

        return $this;
    }
}
