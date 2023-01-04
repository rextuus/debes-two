<?php

namespace App\Entity;

use App\Repository\TransactionStateChangeEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionStateChangeEventRepository::class)]
class TransactionStateChangeEvent
{
    public const TYPE_BANK_ACCOUNT = 'bank';
    public const TYPE_PAYPAL_ACCOUNT = 'paypal';
    public const TYPE_EXCHANGE_ACCOUNT = 'exchange';
    public const TYPE_BLANK = 'blank';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: 'transactionStateChangeEvents', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $transaction;

    #[ORM\Column(type: 'string', length: 255)]
    private $oldState;

    #[ORM\Column(type: 'string', length: 255)]
    private $newState;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\OneToOne(targetEntity: PaymentAction::class, cascade: ['persist', 'remove'])]
    private $paymentTarget;

    #[ORM\OneToOne(targetEntity: Exchange::class, cascade: ['persist', 'remove'])]
    private $exchangeTarget;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getOldState(): ?string
    {
        return $this->oldState;
    }

    public function setOldState(string $oldState): self
    {
        $this->oldState = $oldState;

        return $this;
    }

    public function getNewState(): ?string
    {
        return $this->newState;
    }

    public function setNewState(string $newState): self
    {
        $this->newState = $newState;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPaymentTarget(): ?PaymentAction
    {
        return $this->paymentTarget;
    }

    public function setPaymentTarget(?PaymentAction $paymentTarget): self
    {
        $this->paymentTarget = $paymentTarget;

        return $this;
    }

    public function getExchangeTarget(): ?Exchange
    {
        return $this->exchangeTarget;
    }

    public function setExchangeTarget(?Exchange $exchangeTarget): self
    {
        $this->exchangeTarget = $exchangeTarget;

        return $this;
    }
}
