<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider\Confirm;

use App\Entity\Transaction;
use App\Extension\NextStateProvider\AbstractNextState;
use App\Extension\NextStateProvider\NextStateInterface;
use App\Service\Transaction\TransactionDtos\TransactionDto;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class NextStateDebtConfirm extends AbstractNextState implements NextStateInterface
{
    public const NEXT_STATE_SHORTCUT = Transaction::STATE_CLEARED . AbstractNextState::DEBT_POSTFIX;

    public function getName(): string
    {
        return self::NEXT_STATE_SHORTCUT;
    }

    public function getTwigParameters(TransactionDto $part): array
    {
        $params = ['slug' => $part->getTransactionSlug(),'variant' => 1];
        $acceptLink = $this->router->generate('transfer_overview', $params);
        $acceptButton = 'Hinweis senden';
        $acceptIcon = 'assets/img/email.svg';

        return [
            'acceptLink' => $acceptLink,
            'acceptButton' => $acceptButton,
            'acceptIcon' => $acceptIcon,
            'declineLink' => '',
            'declineButton' => '',
            'declineIcon' => '',
        ];
    }
}
