<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Accepted;

use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\Dto\TransactionDto;


class NextStateDebtAccepted extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_ACCEPTED . AbstractNextState::DEBT_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        $params = ['slug' => $part->getTransactionSlug(),'variant' => 1];
        $acceptLink = $this->router->generate('transfer_overview', $params);
        $acceptButton = 'Begleichen';
        $acceptIcon = 'assets/img/paid.svg';

        return [
            'acceptLink' => $acceptLink,
            'acceptButton' => $acceptButton,
            'acceptIcon' => $acceptIcon,
            'declineLink' => '',
            'declineButton' => '',
            'declineIcon' => '',
            'cardIcon' => 'assets/img/icons/transaction/accept.svg',
        ];
    }
}
