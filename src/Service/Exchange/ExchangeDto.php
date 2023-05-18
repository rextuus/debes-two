<?php

namespace App\Service\Exchange;

use App\Entity\Exchange;
use DateTimeInterface;

/**
 * ExchangeDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class ExchangeDto
{
    /**
     * @var DateTimeInterface
     */
    private $created;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $remainingAmount;

    /**
     * @var string
     */
    private $exchangeSlug;

    /**
     * @var string
     */
    private $exchangePartner;

    /**
     * @var string
     */
    private $exchangeReason;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * create
     *
     * @param Exchange $exchange
     *
     * @return ExchangeDto
     */
    public static function create(Exchange $exchange, string $reason): ExchangeDto
    {
        $dto = new self();

        $dto->setFrom($exchange->getDebt()->getOwner()->getFullName());
        $dto->setTo($exchange->getLoan()->getTransaction()->getDebtor()->getFullName());

        $dto->setExchangePartner($exchange->getTransaction()->getLoaner()->getFullName());
        $dto->setAmount($exchange->getAmount());
        $dto->setCreated($exchange->getCreated());
        $dto->setRemainingAmount($exchange->getRemainingAmount());
        $dto->setExchangeReason($reason);
        $dto->setExchangeSlug($exchange->getTransaction()->getSlug());
        if ($exchange->getTransaction()->getSlug() === $exchange->getLoan()->getTransaction()->getSlug()){
            $dto->setExchangeSlug($exchange->getDebt()->getTransaction()->getSlug());
        }else{
            $dto->setExchangeSlug($exchange->getLoan()->getTransaction()->getSlug());
        }

        return $dto;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): ExchangeDto
    {
        $this->created = $created;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): ExchangeDto
    {
        $this->amount = $amount;
        return $this;
    }

    public function getRemainingAmount(): float
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(float $remainingAmount): ExchangeDto
    {
        $this->remainingAmount = $remainingAmount;
        return $this;
    }

    public function getExchangeSlug(): string
    {
        return $this->exchangeSlug;
    }

    public function setExchangeSlug(string $exchangeSlug): ExchangeDto
    {
        $this->exchangeSlug = $exchangeSlug;
        return $this;
    }

    public function getExchangePartner(): string
    {
        return $this->exchangePartner;
    }

    public function setExchangePartner(string $exchangePartner): ExchangeDto
    {
        $this->exchangePartner = $exchangePartner;
        return $this;
    }

    public function getExchangeReason(): string
    {
        return $this->exchangeReason;
    }

    public function setExchangeReason(string $exchangeReason): ExchangeDto
    {
        $this->exchangeReason = $exchangeReason;
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): ExchangeDto
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): ExchangeDto
    {
        $this->to = $to;
        return $this;
    }

    public function getAmountBefore(): float
    {
        return $this->amount + $this->remainingAmount;
    }
}
