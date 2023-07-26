<?php

namespace App\Service\Transaction\Dto;

use App\Entity\Transaction;
use App\Entity\TransactionStateChangeEvent;
use App\Service\Exchange\ExchangeDto;
use DateTimeInterface;

/**
 * TransactionDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class TransactionDto
{
    private bool $hasMultipleLoaners;
    private bool $hasMultipleDebtors;

    /**
     * @var TransactionPartBaseDto[]
     */
    private array $loanDtos;

    /**
     * @var TransactionPartBaseDto[]
     */
    private array $debtDtos;
    private float $totalAmount;

    private DateTimeInterface $created;

    private ?DateTimeInterface $edited;

    private string $state;

    private string $reason;
    private int $transactionId;

    private string $transactionSlug;

    private string $transactionPartner;

    private bool $isDebtVariant;

    /**
     * @var ExchangeDto[]
     */
    private array $exchangeDtos;

    private float $initialAmount;

    /**
     * @var TransactionStateChangeEvent[]
     */
    private array $changeEvents;

    public function isHasMultipleLoaners(): bool
    {
        return $this->hasMultipleLoaners;
    }

    public function setHasMultipleLoaners(bool $hasMultipleLoaners): void
    {
        $this->hasMultipleLoaners = $hasMultipleLoaners;
    }

    public function isHasMultipleDebtors(): bool
    {
        return $this->hasMultipleDebtors;
    }

    public function setHasMultipleDebtors(bool $hasMultipleDebtors): void
    {
        $this->hasMultipleDebtors = $hasMultipleDebtors;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCreated(): string
    {
        return $this->created->format('d.m.Y');
    }

    public function setCreated(DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): void
    {
        $this->edited = $edited;
    }

    public function getState(): int
    {
        switch ($this->state) {
            case Transaction::STATE_READY:
                return 1;
            case Transaction::STATE_ACCEPTED:
                return 2;
            case Transaction::STATE_CLEARED:
                return 3;
            case Transaction::STATE_CONFIRMED:
                return 4;
            default:
                return 0;
        }
    }

    public function getStateTransactionVariant(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getTransactionId(): int
    {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    public function getTransactionSlug(): string
    {
        return $this->transactionSlug;
    }

    public function setTransactionSlug(string $transactionSlug): void
    {
        $this->transactionSlug = $transactionSlug;
    }

    public function getInitialAmount(): float
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(float $initialAmount): void
    {
        $this->initialAmount = $initialAmount;
    }

    public function getTransactionPartner(): string
    {
        // debt variant
        if ($this->isDebtVariant) {
            // multi
            if ($this->hasMultipleDebtors) {
                $transactionPartnerNames = array_map(
                    function (TransactionPartBaseDto $baseDto) {
                        return $baseDto->getOwner()->getFullName();
                    },
                    $this->getLoanDtos()
                );
                return implode(' | ', $transactionPartnerNames);
            } // single
            else {
                return $this->getLoanDtos()[0]->getOwner()->getFullName();
            }
        } // loan variant
        else {
            // multi
            if ($this->hasMultipleDebtors) {
                $transactionPartnerNames = array_map(
                    function (TransactionPartBaseDto $baseDto) {
                        return $baseDto->getOwner()->getFullName();
                    },
                    $this->getDebtDtos()
                );
                return implode(' | ', $transactionPartnerNames);
            } // single
            else {
                return $this->getDebtDtos()[0]->getOwner()->getFullName();
            }
        }
    }

    public function setTransactionPartner(string $transactionPartner): void
    {
        $this->transactionPartner = $transactionPartner;
    }

    /**
     * @return TransactionPartBaseDto[]
     */
    public function getLoanDtos(): array
    {
        return $this->loanDtos;
    }

    public function setLoanDtos(array $loanDtos): void
    {
        $this->loanDtos = $loanDtos;
    }

    /**
     * @return TransactionPartBaseDto[]
     */
    public function getDebtDtos(): array
    {
        return $this->debtDtos;
    }

    public function setDebtDtos(array $debtDtos): void
    {
        $this->debtDtos = $debtDtos;
    }

    public function isDebtVariant(): bool
    {
        return $this->isDebtVariant;
    }

    public function setIsDebtVariant(bool $isDebtVariant): void
    {
        $this->isDebtVariant = $isDebtVariant;
    }

    /**
     * @return ExchangeDto[]
     */
    public function getExchangeDtos(): array
    {
        return $this->exchangeDtos;
    }

    public function setExchangeDtos(array $exchangeDtos): void
    {
        $this->exchangeDtos = $exchangeDtos;
    }

    public function getSingleTransactionPartner(): ?TransactionPartBaseDto
    {
        // debt variant
        if ($this->isDebtVariant && !$this->hasMultipleDebtors) {
            return $this->getDebtDtos()[0];
        }
        // loan variant
        if (!$this->isDebtVariant && !$this->hasMultipleLoaners) {
            return $this->getLoanDtos()[0];
        }
        return null;
    }

    public function getChangeEvents(): array
    {
        return $this->changeEvents;
    }

    public function setChangeEvents(array $changeEvents): TransactionDto
    {
        $this->changeEvents = $changeEvents;
        return $this;
    }

    public static function createFromTransaction(Transaction $transaction, bool $isDebtVariant): TransactionDto
    {
        $dto = new self();

        $debts = $transaction->getDebts();
        $loans = $transaction->getLoans();
        $dto->setHasMultipleDebtors(count($debts) > 1);
        $dto->setHasMultipleLoaners(count($loans) > 1);

        $debtDtos = array();
        foreach ($debts as $debt) {
            $debtDtos[] = TransactionPartBaseDto::createFromTransactionPart($debt, false);
        }
        $dto->setDebtDtos($debtDtos);

        $loanDtos = array();
        foreach ($loans as $loan) {
            $loanDtos[] = TransactionPartBaseDto::createFromTransactionPart($loan, true);
        }
        $dto->setLoanDtos($loanDtos);

        $dto->setReason($transaction->getReason());
        $dto->setCreated($transaction->getCreated());
        $dto->setEdited($transaction->getEdited());
        $dto->setState($transaction->getState());
        $dto->setTotalAmount($transaction->getAmount());
        $dto->setTransactionId($transaction->getId());
        $dto->setTransactionSlug($transaction->getSlug());
        $dto->setIsDebtVariant($isDebtVariant);
        $dto->setExchangeDtos([]);
        $dto->setInitialAmount($transaction->getInitialAmount());

        return $dto;
    }
}