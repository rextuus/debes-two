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

    #[ORM\OneToMany(mappedBy: 'groupEvent', targetEntity: GroupEventPayment::class, orphanRemoval: true)]
    private Collection $payments;


    public function __construct()
    {
        $this->payments = new ArrayCollection();
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
}
