<?php

namespace App\Entity;

use App\Repository\GroupEventUserCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupEventUserCollectionRepository::class)]
class GroupEventUserCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $initial = null;

    #[ORM\Column]
    private ?bool $allOther = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    private ?GroupEvent $event = null;

    #[ORM\OneToMany(mappedBy: 'debtors', targetEntity: GroupEventPayment::class)]
    private Collection $groupEventPayments;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groupEventUserCollections')]
    private Collection $users;

    public function __construct()
    {
        $this->groupEventPayments = new ArrayCollection();
        $this->users = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function isInitial(): ?bool
    {
        return $this->initial;
    }

    public function setInitial(bool $initial): static
    {
        $this->initial = $initial;

        return $this;
    }

    public function isAllOther(): ?bool
    {
        return $this->allOther;
    }

    public function setAllOther(bool $allOther): static
    {
        $this->allOther = $allOther;

        return $this;
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

    public function getEvent(): ?GroupEvent
    {
        return $this->event;
    }

    public function setEvent(?GroupEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, GroupEventPayment>
     */
    public function getGroupEventPayments(): Collection
    {
        return $this->groupEventPayments;
    }

    public function addGroupEventPayment(GroupEventPayment $groupEventPayment): static
    {
        if (!$this->groupEventPayments->contains($groupEventPayment)) {
            $this->groupEventPayments->add($groupEventPayment);
            $groupEventPayment->setDebtors($this);
        }

        return $this;
    }

    public function removeGroupEventPayment(GroupEventPayment $groupEventPayment): static
    {
        if ($this->groupEventPayments->removeElement($groupEventPayment)) {
            // set the owning side to null (unless already changed)
            if ($groupEventPayment->getDebtors() === $this) {
                $groupEventPayment->setDebtors(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

}
