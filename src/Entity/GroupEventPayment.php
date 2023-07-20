<?php

namespace App\Entity;

use App\Service\GroupEvent\Payment\GroupEventPaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupEventPaymentRepository::class)]
class GroupEventPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groupEventPayments')]
    private ?User $loaner = null;

    #[ORM\ManyToOne( inversedBy: 'groupEventPayments')]
    private ?GroupEventUserCollection $debtors = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'payments', cascade: ['persist'],)]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroupEvent $groupEvent = null;

    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDebtors(): ?GroupEventUserCollection
    {
        return $this->debtors;
    }

    public function setDebtors(?GroupEventUserCollection $debtors): static
    {
        $this->debtors = $debtors;

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

    public function getGroupEvent(): ?GroupEvent
    {
        return $this->groupEvent;
    }

    public function setGroupEvent(?GroupEvent $groupEvent): static
    {
        $this->groupEvent = $groupEvent;

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
}
