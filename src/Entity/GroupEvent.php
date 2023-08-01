<?php

namespace App\Entity;

use App\Repository\GroupEventRepository;
use DateTimeInterface;
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created = null;

    #[ORM\ManyToOne(inversedBy: 'groupEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\Column]
    private ?bool $open = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'groupEvent', targetEntity: GroupEventPayment::class)]
    private Collection $groupEventPayments;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: GroupEventUserCollection::class, cascade: ['persist'])]
    private Collection $userGroups;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: GroupEventResult::class)]
    private Collection $groupEventResults;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $evaluated = null;

    public function __construct()
    {
        $this->groupEventPayments = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->groupEventResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): static
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

    public function isOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): static
    {
        $this->open = $open;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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
            $groupEventPayment->setGroupEvent($this);
        }

        return $this;
    }

    public function removeGroupEventPayment(GroupEventPayment $groupEventPayment): static
    {
        if ($this->groupEventPayments->removeElement($groupEventPayment)) {
            // set the owning side to null (unless already changed)
            if ($groupEventPayment->getGroupEvent() === $this) {
                $groupEventPayment->setGroupEvent(null);
            }
        }

        return $this;
    }

    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(GroupEventUserCollection $group): static
    {
        if (!$this->userGroups->contains($group)) {
            $this->userGroups->add($group);
            $group->setEvent($this);
        }

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers (): array
    {
        foreach ($this->getUserGroups() as $collection){
            /**
             * @var GroupEventUserCollection $collection
             */
            if ($collection->isInitial()){
                return $collection->getUsers()->toArray();
            }
        }
        return [];
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

    public function getEvaluated(): ?DateTimeInterface
    {
        return $this->evaluated;
    }

    public function setEvaluated(DateTimeInterface|null $evaluated): static
    {
        $this->evaluated = $evaluated;

        return $this;
    }
}
