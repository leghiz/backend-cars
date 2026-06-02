<?php

namespace App\Entity;

use App\Repository\LotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LotRepository::class)]
class Lot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Modification $modification = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $sold_date = null;

    #[ORM\Column(length: 50)]
    private ?string $body_number = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToOne(mappedBy: 'lot', cascade: ['persist', 'remove'])]
    private ?Background $background = null;

    /**
     * @var Collection<int, CarMedia>
     */
    #[ORM\OneToMany(targetEntity: CarMedia::class, mappedBy: 'lot')]
    private Collection $carMedia;

    #[ORM\OneToOne(mappedBy: 'lot', cascade: ['persist', 'remove'])]
    private ?Review $review = null;

    public function __construct()
    {
        $this->carMedia = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModification(): ?Modification
    {
        return $this->modification;
    }

    public function setModification(?Modification $modification): static
    {
        $this->modification = $modification;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSoldDate(): ?\DateTime
    {
        return $this->sold_date;
    }

    public function setSoldDate(?\DateTime $sold_date): static
    {
        $this->sold_date = $sold_date;

        return $this;
    }

    public function getBodyNumber(): ?string
    {
        return $this->body_number;
    }

    public function setBodyNumber(string $body_number): static
    {
        $this->body_number = $body_number;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getBackground(): ?Background
    {
        return $this->background;
    }

    public function setBackground(Background $background): static
    {
        // set the owning side of the relation if necessary
        if ($background->getLot() !== $this) {
            $background->setLot($this);
        }

        $this->background = $background;

        return $this;
    }

    /**
     * @return Collection<int, CarMedia>
     */
    public function getCarMedia(): Collection
    {
        return $this->carMedia;
    }

    public function addCarMedium(CarMedia $carMedium): static
    {
        if (!$this->carMedia->contains($carMedium)) {
            $this->carMedia->add($carMedium);
            $carMedium->setLot($this);
        }

        return $this;
    }

    public function removeCarMedium(CarMedia $carMedium): static
    {
        if ($this->carMedia->removeElement($carMedium)) {
            // set the owning side to null (unless already changed)
            if ($carMedium->getLot() === $this) {
                $carMedium->setLot(null);
            }
        }

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(Review $review): static
    {
        // set the owning side of the relation if necessary
        if ($review->getLot() !== $this) {
            $review->setLot($this);
        }

        $this->review = $review;

        return $this;
    }
}
