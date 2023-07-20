<?php

namespace App\Entity;

use App\Service\GroupEvent\GroupEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupEventRepository::class)]
class GroupEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $decscription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(inversedBy: 'groupEvents')]
    private ?User $creator = null;

    #[ORM\OneToMany(mappedBy: 'groupEvent', targetEntity: GroupEventPayment::class, cascade: ['persist'], orphanRemoval: true,)]
    private Collection $payments;

    #[ORM\ManyToMany(targetEntity: GroupEventUserCollection::class, inversedBy: 'groupEvents')]
    private Collection $participantGroups;

    #[ORM\Column]
    private ?bool $open = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: GroupEventResult::class)]
    private Collection $groupEventResults;


    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->participantGroups = new ArrayCollection();
        $this->groupEventResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDecscription(): ?string
    {
        return $this->decscription;
    }

    public function setDecscription(string $decscription): static
    {
        $this->decscription = $decscription;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, GroupEventPayment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(GroupEventPayment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setGroupEvent($this);
        }

        return $this;
    }

    public function removePayment(GroupEventPayment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getGroupEvent() === $this) {
                $payment->setGroupEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GroupEventUserCollection>
     */
    public function getParticipantGroups(): Collection
    {
        return $this->participantGroups;
    }

    public function addParticipantGroup(GroupEventUserCollection $participantGroup): static
    {
        if (!$this->participantGroups->contains($participantGroup)) {
            $this->participantGroups->add($participantGroup);
        }

        return $this;
    }

    public function removeParticipantGroup(GroupEventUserCollection $participantGroup): static
    {
        $this->participantGroups->removeElement($participantGroup);

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        foreach ($this->getParticipantGroups()->toArray() as $group){
            if ($group->isInitial()){
                return $group->getUsers()->toArray();
            }
        }
        return [];
    }

    public function isOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): static
    {
        $this->open = $open;

        return $this;
    }

    /**
     * @return Collection<int, GroupEventResult>
     */
    public function getGroupEventResults(): Collection
    {
        return $this->groupEventResults;
    }

    public function addGroupEventResult(GroupEventResult $groupEventResult): static
    {
        if (!$this->groupEventResults->contains($groupEventResult)) {
            $this->groupEventResults->add($groupEventResult);
            $groupEventResult->setEvent($this);
        }

        return $this;
    }

    public function removeGroupEventResult(GroupEventResult $groupEventResult): static
    {
        if ($this->groupEventResults->removeElement($groupEventResult)) {
            // set the owning side to null (unless already changed)
            if ($groupEventResult->getEvent() === $this) {
                $groupEventResult->setEvent(null);
            }
        }

        return $this;
    }
}
