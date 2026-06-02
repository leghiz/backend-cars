<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $is_verified = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    #[ORM\OneToOne(mappedBy: 'account', cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'account')]
    private Collection $reviews;

    /**
     * @var Collection<int, Request>
     */
    #[ORM\ManyToMany(targetEntity: Request::class, mappedBy: 'account')]
    private Collection $requests;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->requests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): static
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getAccount() !== $this) {
            $profile->setAccount($this);
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAccount($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAccount() === $this) {
                $review->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->addAccount($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            $request->removeAccount($this);
        }

        return $this;
    }
}
