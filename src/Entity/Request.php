<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'requests')]
    private Collection $account;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?CarModel $car_model = null;

    #[ORM\Column(length: 255)]
    private ?string $car_name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $call_time = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->account = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAccount(): Collection
    {
        return $this->account;
    }

    public function addAccount(User $account): static
    {
        if (!$this->account->contains($account)) {
            $this->account->add($account);
        }

        return $this;
    }

    public function removeAccount(User $account): static
    {
        $this->account->removeElement($account);

        return $this;
    }

    public function getCarModel(): ?CarModel
    {
        return $this->car_model;
    }

    public function setCarModel(?CarModel $car_model): static
    {
        $this->car_model = $car_model;

        return $this;
    }

    public function getCarName(): ?string
    {
        return $this->car_name;
    }

    public function setCarName(string $car_name): static
    {
        $this->car_name = $car_name;

        return $this;
    }

    public function getCallTime(): ?\DateTime
    {
        return $this->call_time;
    }

    public function setCallTime(?\DateTime $call_time): static
    {
        $this->call_time = $call_time;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
}
