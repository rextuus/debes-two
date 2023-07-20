<?php

namespace App\Entity;

use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupEventUserCollectionRepository::class)]
class GroupEventUserCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groupEventUserCollections')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'debtors', targetEntity: GroupEventPayment::class)]
    private Collection $groupEventPayments;

    #[ORM\Column]
    private ?bool $initial = false;

    #[ORM\ManyToMany(targetEntity: GroupEvent::class, mappedBy: 'participantGroups')]
    private Collection $groupEvents;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $allOthers = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groupEventPayments = new ArrayCollection();
        $this->groupEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isInitial(): ?bool
    {
        return $this->initial;
    }

    public function setInitial(bool $initial): static
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * @return Collection<int, GroupEvent>
     */
    public function getGroupEvents(): Collection
    {
        return $this->groupEvents;
    }

    public function addGroupEvent(GroupEvent $groupEvent): static
    {
        if (!$this->groupEvents->contains($groupEvent)) {
            $this->groupEvents->add($groupEvent);
            $groupEvent->addParticipantGroup($this);
        }

        return $this;
    }

    public function removeGroupEvent(GroupEvent $groupEvent): static
    {
        if ($this->groupEvents->removeElement($groupEvent)) {
            $groupEvent->removeParticipantGroup($this);
        }

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

    public function isAllOthers(): ?bool
    {
        return $this->allOthers;
    }

    public function setAllOthers(bool $allOthers): static
    {
        $this->allOthers = $allOthers;

        return $this;
    }
}
