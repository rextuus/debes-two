<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\Transaction;
use App\Service\Transaction\TransactionDtos\TransactionDto;
use App\Service\Transaction\TransactionService;
use App\Service\Transfer\TransferService;
use App\Service\Util\TimeConverter;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class TransferExtension extends AbstractExtension
{
    public function __construct(
        private Environment $environment,
        private TimeConverter $timeConverter,
        private TransferService $transferService,
        private TransactionService $transactionService,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('transfer_bank', [$this, 'renderBankTransfer']),
            new TwigFunction('transfer_paypal', [$this, 'renderPaypalTransfer']),
            new TwigFunction('transfer_exchange', [$this, 'renderExchange']),
        ];
    }

    public function renderBankTransfer(TransactionDto $part, FormView $bankForm): string
    {
        return $this->environment->render(
            'extension/transfer/transfer_bank.html.twig',
            [
                'dto' => $part,
                'bankForm' => $bankForm,
            ]
        );
    }

    public function renderPaypalTransfer(TransactionDto $part, FormView $paypalForm): string
    {
        return $this->environment->render(
            'extension/transfer/transfer_paypal.html.twig',
            [
                'dto' => $part,
                'paypalForm' => $paypalForm,
            ]
        );
    }

    public function renderExchange(TransactionDto $part, FormView $exchangeForm): string
    {
        return $this->environment->render(
            'extension/transfer/transfer_exchange.html.twig',
            [
                'dto' => $part,
                'exchangeForm' => $exchangeForm,
            ]
        );
    }

    private function buildInfoText(TransactionDto $part): string
    {
        if ($part->getState() === Transaction::DTO_MAPPING[Transaction::STATE_CLEARED]) {
            if ($part->isDebtVariant()) {
                return 'Du hast diese Schuld bereits bezahlt und die Gegenpartei muss das nur noch bestätigen';
            }
            return 'Die wurde das Geld für diese Transaktion bereits überwiesen. Bestätige bitte den Eingang!';
        }

        $template = 'Du bekommst von %s noch %.2f € für %s';
        if ($part->isDebtVariant()) {
            $template = 'Du schuldest %s noch %.2f € für %s';
        }
        return sprintf(
            $template,
            $part->getTransactionPartner(),
            $part->getTotalAmount(),
            $part->getReason()
        );
    }
}