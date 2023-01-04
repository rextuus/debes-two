<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'transactions')]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{

    const STATE_CREATED = 'created';
    const STATE_READY = 'ready';
    const STATE_ACCEPTED = 'accepted';
    const STATE_PARTIAL_ACCEPTED = 'partial_accepted';
    const STATE_DECLINED = 'declined';
    const STATE_PARTIAL_CLEARED = 'partial_cleared';
    const STATE_CLEARED = 'cleared';
    const STATE_CONFIRMED = 'confirmed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'string', length: 255)]
    private $state;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\OneToMany(targetEntity: Debt::class, mappedBy: 'transaction', cascade: ['persist'])]
    private $debts;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'transaction', cascade: ['persist'])]
    private $loans;

    #[ORM\Column(type: 'string', length: 255)]
    private $reason;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $edited;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private $slug;

    #[ORM\Column(type: 'float')]
    private $initialAmount;

    #[ORM\OneToMany(targetEntity: Exchange::class, mappedBy: 'transaction')]
    private $exchanges;

    #[ORM\OneToMany(targetEntity: TransactionStateChangeEvent::class, mappedBy: 'transaction', cascade: ['persist'])]
    private $transactionStateChangeEvents;

    #[ORM\OneToMany(targetEntity: PaymentAction::class, mappedBy: 'transaction')]
    private $paymentActions;

    public function __construct()
    {
        $this->debts = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->exchanges = new ArrayCollection();
        $this->transactionStateChangeEvents = new ArrayCollection();
        $this->paymentActions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Collection|Debt[]
     */
    public function getDebts(): Collection
    {
        return $this->debts;
    }

    public function addDebt(Debt $debt): self
    {
        if (!$this->debts->contains($debt)) {
            $this->debts[] = $debt;
            $debt->setTransaction($this);
        }

        return $this;
    }

    public function removeDebt(Debt $debt): self
    {
        if ($this->debts->removeElement($debt)) {
            // set the owning side to null (unless already changed)
            if ($debt->getTransaction() === $this) {
                $debt->setTransaction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Loan[]
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): self
    {
        if (!$this->loans->contains($loan)) {
            $this->loans[] = $loan;
            $loan->setTransaction($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getTransaction() === $this) {
                $loan->setTransaction(null);
            }
        }

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): self
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getReason();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * getLoaner
     *
     * @return User
     */
    public function getLoaner(): User
    {
        return $this->loans[0]->getOwner();
    }

    /**
     * getDebtor
     *
     * @return User
     */
    public function getDebtor(): User
    {
        return $this->debts[0]->getOwner();
    }

    /**
     * getDebtors
     *
     * @return array
     */
    public function getDebtorIds(): array
    {
        $debtors = array();
        foreach ($this->getDebts() as $debt) {
            $debtors[] = $debt->getOwner()->getId();
        }
        return $debtors;
    }

    /**
     * getLoaners
     *
     * @return array
     */
    public function getLoanerIds(): array
    {
        $loaners = array();
        foreach ($this->getLoans() as $loans) {
            $loaners[] = $loans->getOwner()->getId();
        }
        return $loaners;
    }

    public function getInitialAmount(): ?float
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(float $initialAmount): self
    {
        $this->initialAmount = $initialAmount;

        return $this;
    }

    /**
     * getDebtors
     *
     * @return array
     */
    public function getDebtors(): array
    {
        $debtors = array();
        foreach ($this->getDebts() as $debt) {
            $debtors[] = $debt->getOwner();
        }
        return $debtors;
    }

    /**
     * getLoaners
     *
     * @return array
     */
    public function getLoaners(): array
    {
        $loaners = array();
        foreach ($this->getLoans() as $loans) {
            $loaners[] = $loans->getOwner();
        }
        return $loaners;
    }

    public function isDebtTheLastNonAcceptedOne(Debt $debtToCheck): bool
    {
        $allowedStates = [self::STATE_ACCEPTED];
        $debtsWithIncorrectState = 0;
        foreach ($this->getDebts() as $debt) {
            if (!in_array($debt->getState(), $allowedStates)) {
                $debtsWithIncorrectState++;
            }
        }
        // all greater 1 (our last debt) means we cant update to accept
        return $debtsWithIncorrectState < 2 && $debtToCheck->getState() === self::STATE_READY;
    }

    public function isDebtTheLastNonClearedOne(Debt $debtToCheck): bool
    {
        $allowedStates = [self::STATE_CLEARED];
        $debtsWithIncorrectState = 0;
        foreach ($this->getDebts() as $debt) {
            if (!in_array($debt->getState(), $allowedStates)) {
                $debtsWithIncorrectState++;
            }
        }

        // all greater 1 (our last debt) means we cant update to accept
        return $debtsWithIncorrectState < 2 && $debtToCheck->getState() === self::STATE_ACCEPTED;
    }

    /**
     * hasMultipleLoaners
     *
     * @return bool
     */
    public function hasMultipleLoaners(): bool
    {
        return count($this->loans) > 1;
    }

    /**
     * hasMultipleDebtors
     *
     * @return bool
     */
    public function hasMultipleDebtors(): bool
    {
        return count($this->debts) > 1;
    }

    /**
     * isMultipleTransaction
     *
     * @return bool
     */
    public function isMultipleTransaction(): bool
    {
        return $this->hasMultipleLoaners() && $this->hasMultipleDebtors();
    }

    /**
     * isMultipleTransaction
     *
     * @return bool
     */
    public function hasMultipleSide(): bool
    {
        return $this->hasMultipleLoaners() || $this->hasMultipleDebtors();
    }

    /**
     * isSingleTransaction
     *
     * @return bool
     */
    public function isSingleTransaction(): bool
    {
        return !$this->hasMultipleLoaners() && !$this->hasMultipleDebtors();
    }

    /**
     * @return Collection|Exchange[]
     */
    public function getExchanges(): Collection
    {
        return $this->exchanges;
    }

    public function addExchange(Exchange $exchange): self
    {
        if (!$this->exchanges->contains($exchange)) {
            $this->exchanges[] = $exchange;
            $exchange->setTransaction($this);
        }

        return $this;
    }

    public function removeExchange(Exchange $exchange): self
    {
        if ($this->exchanges->removeElement($exchange)) {
            // set the owning side to null (unless already changed)
            if ($exchange->getTransaction() === $this) {
                $exchange->setTransaction(null);
            }
        }

        return $this;
    }

    public function getTransactionPartByUser(User $transactionPartOwner){
        foreach ($this->getDebts() as $debt){
            if ($debt->getOwner() === $transactionPartOwner){
                return $debt;
            }
        }
        foreach ($this->getLoans() as $loan){
            if ($loan->getOwner() === $transactionPartOwner){
                return $loan;
            }
        }
        return null;
    }

    /**
     * @return Collection|TransactionStateChangeEvent[]
     */
    public function getTransactionStateChangeEvents(): Collection
    {
        return $this->transactionStateChangeEvents;
    }

    public function addTransactionStateChangeEvent(TransactionStateChangeEvent $transactionStateChangeEvent): self
    {
        if (!$this->transactionStateChangeEvents->contains($transactionStateChangeEvent)) {
            $this->transactionStateChangeEvents[] = $transactionStateChangeEvent;
            $transactionStateChangeEvent->setTransaction($this);
        }

        return $this;
    }

    public function removeTransactionStateChangeEvent(TransactionStateChangeEvent $transactionStateChangeEvent): self
    {
        if ($this->transactionStateChangeEvents->removeElement($transactionStateChangeEvent)) {
            // set the owning side to null (unless already changed)
            if ($transactionStateChangeEvent->getTransaction() === $this) {
                $transactionStateChangeEvent->setTransaction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PaymentAction[]
     */
    public function getPaymentActions(): Collection
    {
        return $this->paymentActions;
    }

    public function addPaymentAction(PaymentAction $paymentAction): self
    {
        if (!$this->paymentActions->contains($paymentAction)) {
            $this->paymentActions[] = $paymentAction;
            $paymentAction->setTransaction($this);
        }

        return $this;
    }

    public function removePaymentAction(PaymentAction $paymentAction): self
    {
        if ($this->paymentActions->removeElement($paymentAction)) {
            // set the owning side to null (unless already changed)
            if ($paymentAction->getTransaction() === $this) {
                $paymentAction->setTransaction(null);
            }
        }

        return $this;
    }
}
