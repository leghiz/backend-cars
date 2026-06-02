<?php

namespace App\Entity;

use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, CarModel>
     */
    #[ORM\OneToMany(targetEntity: CarModel::class, mappedBy: 'manufacturer')]
    private Collection $carModels;

    public function __construct()
    {
        $this->carModels = new ArrayCollection();
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

    /**
     * @return Collection<int, CarModel>
     */
    public function getCarModels(): Collection
    {
        return $this->carModels;
    }

    public function addCarModel(CarModel $carModel): static
    {
        if (!$this->carModels->contains($carModel)) {
            $this->carModels->add($carModel);
            $carModel->setManufacturer($this);
        }

        return $this;
    }

    public function removeCarModel(CarModel $carModel): static
    {
        if ($this->carModels->removeElement($carModel)) {
            // set the owning side to null (unless already changed)
            if ($carModel->getManufacturer() === $this) {
                $carModel->setManufacturer(null);
            }
        }

        return $this;
    }
}
