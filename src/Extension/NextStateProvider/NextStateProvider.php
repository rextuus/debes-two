<?php

declare(strict_types=1);

namespace App\Extension\NextStateProvider;

use App\Service\Transaction\Dto\TransactionDto;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;


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
        if ($part->isDebtVariant()) {
            $postfix = AbstractNextState::DEBT_POSTFIX;
        }
        foreach ($this->handlers as $handler) {
            if ($handler->getName() === $part->getStateTransactionVariant() . $postfix) {
                return $handler;
            }
        }
        return null;
    }
}
