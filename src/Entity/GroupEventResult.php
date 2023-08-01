<?php

namespace App\Entity;

use App\Repository\GroupEventResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupEventResultRepository::class)]
class GroupEventResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groupEventResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroupEvent $event = null;

    #[ORM\ManyToOne(inversedBy: 'groupEventResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $debtor = null;

    #[ORM\ManyToOne(inversedBy: 'groupEventResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $loaner = null;

    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    #[ORM\Column]
    private ?float $amount = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDebtor(): ?User
    {
        return $this->debtor;
    }

    public function setDebtor(?User $debtor): static
    {
        $this->debtor = $debtor;

        return $this;
    }

    public function getLoaner(): ?User
    {
        return $this->loaner;
    }

    public function setLoaner(?User $loaner): static
    {
        $this->loaner = $loaner;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
