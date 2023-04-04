<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Accept;

use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\TransactionDtos\TransactionDto;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class NextStateDebtAccept extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_READY . AbstractNextState::DEBT_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        $params = ['slug' => $part->getTransactionSlug(),'variant' => 'debtor'];
        $acceptLink = $this->router->generate('transaction_accept', $params);
        $acceptButton = 'Akzeptieren';
        $acceptIcon = 'assets/img/accept2.svg';

        $params = ['slug' => $part->getTransactionSlug(), 'variant' => 'loaner'];
        $declineLink = $this->router->generate('transaction_accept', $params);
        $declineButton = 'Ablehnen';
        $declineIcon = 'assets/img/warning.svg';

        return [
            'acceptLink' => $acceptLink,
            'acceptButton' => $acceptButton,
            'acceptIcon' => $acceptIcon,
            'declineLink' => $declineLink,
            'declineButton' => $declineButton,
            'declineIcon' => $declineIcon,
        ];
    }
}