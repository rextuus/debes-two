<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\GroupEventPayment;
use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventData
{
    private string $description;

    /**
     * @var GroupEventPayment[]
     */
    private array $payments;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): GroupEventData
    {
        $this->description = $description;
        return $this;
    }

    public function getPayments(): array
    {
        return $this->payments;
    }

    public function setPayments(array $payments): GroupEventData
    {
        $this->payments = $payments;
        return $this;
    }
}