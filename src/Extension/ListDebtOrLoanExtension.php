<?php

namespace App\Extension;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionService;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ListDebtOrLoanExtension
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class ListDebtOrLoanExtension extends AbstractExtension
{
    public function __construct(private TransactionService $transactionService)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_debt_or_loan_list',
                [$this, 'renderDebtOrLoanList'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    public function renderDebtOrLoanList(
        Environment $environment,
        User        $owner,
        bool        $debtVariant,
        string      $state
    ): string
    {

        if ($debtVariant) {
            $ready = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_READY);
            $confirmed = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_CONFIRMED);
            $accepted = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_ACCEPTED);
            $cleared = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_CLEARED);
            $headerClass = 'debt-header';
        } else {
            $ready = $this->transactionService->getAllLoanTransactionsForUserAndState2($owner, Transaction::STATE_READY);
            $confirmed = $this->transactionService->getAllLoanTransactionsForUserAndState2($owner, Transaction::STATE_CONFIRMED);
            $accepted = $this->transactionService->getAllLoanTransactionsForUserAndState2($owner, Transaction::STATE_ACCEPTED);
            $cleared = $this->transactionService->getAllLoanTransactionsForUserAndState2($owner, Transaction::STATE_CLEARED);
            $headerClass = 'loan-header';
        }

        $tabClasses = ['active', '', '', ''];
        switch ($state) {
            case Transaction::STATE_ACCEPTED:
                $tabClasses = ['', 'active', '', ''];
                break;
            case Transaction::STATE_CLEARED:
                $tabClasses = ['', '', 'active', ''];
                break;
            case Transaction::STATE_CONFIRMED:
                $tabClasses = ['', '', '', 'active'];
                break;
        }

        return $environment->render(
            'transaction/transaction.list.extension.html.twig',
            [
                'debtVariant' => $debtVariant,
                'ready' => $ready,
                'confirmed' => $confirmed,
                'accepted' => $accepted,
                'cleared' => $cleared,
                'tabClasses' => $tabClasses,
                'headerClass' => $headerClass,
            ]
        );
    }
}