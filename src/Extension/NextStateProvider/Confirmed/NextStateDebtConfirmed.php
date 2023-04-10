<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Confirmed;

use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\TransactionDtos\TransactionDto;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class NextStateDebtConfirmed extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_CONFIRMED . AbstractNextState::DEBT_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        return [
            'acceptLink' => '',
            'acceptButton' => '',
            'acceptIcon' => '',
            'declineLink' => '',
            'declineButton' => '',
            'declineIcon' => '',
            'cardIcon' => 'assets/img/party.svg',
        ];
    }
}
