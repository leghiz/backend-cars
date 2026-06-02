<?php

namespace App\Entity;

use App\Repository\CarModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarModelRepository::class)]
class CarModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'carModels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manufacturer $manufacturer = null;

    /**
     * @var Collection<int, Modification>
     */
    #[ORM\OneToMany(targetEntity: Modification::class, mappedBy: 'model')]
    private Collection $modifications;

    public function __construct()
    {
        $this->modifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @return Collection<int, Modification>
     */
    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    public function addModification(Modification $modification): static
    {
        if (!$this->modifications->contains($modification)) {
            $this->modifications->add($modification);
            $modification->setModel($this);
        }

        return $this;
    }

    public function removeModification(Modification $modification): static
    {
        if ($this->modifications->removeElement($modification)) {
            // set the owning side to null (unless already changed)
            if ($modification->getModel() === $this) {
                $modification->setModel(null);
            }
        }

        return $this;
    }
}
