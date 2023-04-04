<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider;

use App\Service\Transaction\TransactionDtos\TransactionDto;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class NextStateProvider
{
    private $handlers;

    public function __construct(
        #[TaggedIterator('next.state')] iterable $handlers
    ) {
        /** @var NextStateInterface[] $this->handlers */
        $this->handlers = $handlers;
    }

    public function getHandlerForState(TransactionDto $part): ?NextStateInterface
    {
        $postfix = AbstractNextState::LOAN_POSTFIX;
        if ($part->isDebtVariant()){
            $postfix = AbstractNextState::DEBT_POSTFIX;
        }
        foreach ($this->handlers as $handler) {
            if ($handler->getName() === $part->getStateTransactionVariant().$postfix){
                return $handler;
            }
        }
        return null;
    }
}
