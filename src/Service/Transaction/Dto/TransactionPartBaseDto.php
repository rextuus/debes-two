<?php

namespace App\Service\Transaction\Dto;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Exchange\ExchangeDto;
use App\Service\Loan\LoanDto;
use DateTimeInterface;

class TransactionPartBaseDto
{
    private float $amount;

    private DateTimeInterface $created;

    private ?DateTimeInterface $edited;

    private string $state;

    /**
     * @var ExchangeDto[]
     */
    private array $exchangeDtos;

    private bool $isLoanDto;

    private User $owner;

    public static function createFromTransactionPart(
        TransactionPartInterface $transactionPart,
        bool                     $isLoanDto = true
    ): TransactionPartBaseDto
    {
        $dto = new self();
        $dto->setCreated($transactionPart->getCreated());
        $dto->setEdited($transactionPart->getEdited());
        $dto->setAmount($transactionPart->getAmount());
        $dto->setState($transactionPart->getState());
        $dto->setOwner($transactionPart->getOwner());
        $dto->setIsLoanDto($isLoanDto);

        return $dto;
    }


    /**
     * @return int
     */
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): TransactionPartBaseDto
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): TransactionPartBaseDto
    {
        $this->created = $created;
        return $this;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(?DateTimeInterface $edited): TransactionPartBaseDto
    {
        $this->edited = $edited;
        return $this;
    }

    public function getExchangeDtos(): array
    {
        return $this->exchangeDtos;
    }

    public function setExchangeDtos(array $exchangeDtos): TransactionPartBaseDto
    {
        $this->exchangeDtos = $exchangeDtos;
        return $this;
    }

    public function isLoanDto(): bool
    {
        if ($this instanceof LoanDto){
            return true;
        }
        return false;
    }

    public function setIsLoanDto(bool $isLoanDto): TransactionPartBaseDto
    {
        $this->isLoanDto = $isLoanDto;
        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): TransactionPartBaseDto
    {
        $this->owner = $owner;
        return $this;
    }

    private function setState(string $state)
    {
        $this->state = $state;
        return $this;
    }
}
