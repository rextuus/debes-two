<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Ready;

use App\Controller\TransactionController;
use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\TransactionDtos\TransactionDto;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class NextStateLoanReady extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_READY . AbstractNextState::LOAN_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        $params = ['slug' => $part->getTransactionSlug(),'variant' => TransactionController::REQUESTER_VARIANT_LOANER];
        $acceptLink = $this->router->generate('transaction_notify', $params);
        $acceptButton = 'Erinnern';
        $acceptIcon = 'assets/img/email.svg';

        return [
            'acceptLink' => $acceptLink,
            'acceptButton' => $acceptButton,
            'acceptIcon' => $acceptIcon,
            'declineLink' => '',
            'declineButton' => '',
            'declineIcon' => '',
            'cardIcon' => 'assets/img/create.svg',
        ];
    }
}
