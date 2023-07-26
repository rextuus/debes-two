<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Cleared;

use App\Controller\TransactionController;
use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\Dto\TransactionDto;


class NextStateDebtCleared extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_CLEARED . AbstractNextState::DEBT_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        $params = ['slug' => $part->getTransactionSlug(),'variant' => TransactionController::REQUESTER_VARIANT_DEBTOR];
        $acceptLink = $this->router->generate('transaction_notify', $params);
        $acceptButton = 'Hinweis senden';
        $acceptIcon = 'assets/img/email.svg';

        return [
            'acceptLink' => $acceptLink,
            'acceptButton' => $acceptButton,
            'acceptIcon' => $acceptIcon,
            'declineLink' => '',
            'declineButton' => '',
            'declineIcon' => '',
            'cardIcon' => 'assets/img/paid.svg',
        ];
    }
}
