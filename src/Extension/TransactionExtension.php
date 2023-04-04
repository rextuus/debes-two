<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\TransactionStateChangeEvent;
use App\Extension\NextStateProvider\NextStateProvider;
use App\Service\Transaction\TransactionDtos\TransactionDto;
use App\Service\Util\TimeConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class TransactionExtension extends AbstractExtension
{
    public function __construct(
        private Environment           $environment,
        private TimeConverter         $timeConverter,
        private UrlGeneratorInterface $router,
        private NextStateProvider $nextStateProvider,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('transaction_part_content_card', [$this, 'renderTransactionPartContentCard']),
            new TwigFunction('transaction_part_summary', [$this, 'renderTransactionPartSummary']),
            new TwigFunction('transaction_part_details', [$this, 'renderTransactionPartDetails']),
        ];
    }

    public function renderTransactionPartContentCard(TransactionDto $part): string
    {
        $acceptButton = 'Ja';
        $declineButton = 'Nein';
        $acceptLink = 'transfer_overview';
        $declineLink = 'transaction_process';
        $acceptIcon = 'assets/img/accept.svg';
        $declineIcon = 'assets/img/warning.svg';

        $cardIcon = 'assets/img/create.svg';
//        $handlerName = '';
//        switch ($part->getState()) {
//            case 1:
//                if ($part->isDebtVariant()) {
//                    $handlerName = 'debt_accept';
//                } else {
//                    $handlerName = 'loan_accept';
//                }
//                break;
//        }
        $contentCardParams = ($this->nextStateProvider->getHandlerForState($part)->getTwigParameters($part));

//        switch ($part->getState()) {
//            case 1:
//                // accepted => pay/break | remember
//                $cardIcon = 'assets/img/create.svg';
//                if ($part->isDebtVariant()) {
//                    $params = ['slug' => $part->getTransactionSlug(),'variant' => 'debtor'];
//                    $acceptLink = $this->router->generate('transaction_accept', $params);
//
//                    $acceptButton = 'Akzeptieren';
//                    $acceptIcon = 'assets/img/paid.svg';
//
//                    $params = ['slug' => $part->getTransactionSlug(), 'variant' => 'loaner'];
//                    $declineLink = $this->router->generate('transaction_accept', $params);
//                    $declineButton = 'Ablehnen';
//                    $declineIcon = 'assets/img/warning.svg';
//                } else {
//                    $acceptLink = 'transfer_overview';
//                    $acceptButton = 'Erinnern';
//                    $acceptIcon = 'assets/img/email.svg';
//
//                    $declineLink = '';
//                }
//
//                break;
//            case 2:
//                // accepted => pay/break | remember
//                $cardIcon = 'assets/img/accept.svg';
//                if ($part->isDebtVariant()) {
//                    $acceptLink = 'transfer_overview';
//                    $acceptButton = 'Bezahlen';
//                    $acceptIcon = 'assets/img/paid.svg';
//
//                    $declineLink = 'transaction_process';
//                    $declineButton = 'Reklamieren';
//                    $declineIcon = 'assets/img/warning.svg';
//                } else {
//                    $acceptLink = 'transfer_overview';
//                    $acceptButton = 'Erinnern';
//                    $acceptIcon = 'assets/img/email.svg';
//
//                    $declineLink = '';
//                }
//
//                break;
//            case 3:
//                // paid => remember | confirm
//                if ($part->isDebtVariant()) {
//                    $acceptLink = '';
//
//                    $declineLink = 'transaction_process';
//                    $declineButton = 'Hinweis senden';
//                    $declineIcon = 'assets/img/warning.svg';
//                } else {
//                    $acceptLink = 'transaction_process';
//                    $acceptButton = 'Bestätigen';
//                    $acceptIcon = 'assets/img/party.svg';
//
//                    $declineLink = 'transaction_process';
//                    $declineButton = 'Bemängeln';
//                    $declineIcon = 'assets/img/warning.svg';
//                }
//
//                $cardIcon = 'assets/img/paid.svg';
//                break;
//        }

        return $this->environment->render(
            'extension/transaction_part_card.html.twig',
            array_merge(
                [
                    'part' => $part,
                    'ago' => $this->timeConverter->getUserFriendlyDateTime($part->getEdited()),
                    'slug' => $part->getTransactionSlug(),
                    'cardIcon' => $cardIcon,
                    'infoText' => $this->buildInfoText($part)
                ],
                $contentCardParams
            )
        );
    }

    public function renderTransactionPartSummary(TransactionDto $part): string
    {
        return $this->environment->render(
            'extension/transaction_part_summary.html.twig',
            [
                'dto' => $part,
                'debtVariant' => $part->isDebtVariant(),
            ]
        );
    }

    public function renderTransactionPartDetails(TransactionDto $part, bool $prefixed = true): string
    {
        $created = $part->getCreated() ?: 'Erstellt';
        if ($prefixed && $part->getCreated()) {
            $created = 'Erstellt am ' . $part->getCreated();
        }

        $accepted = 'Akzeptiert';
        $isAccepted = '';
        $cleared = 'Bezahlt';
        $isCleared = '';
        $confirmed = 'Abgewickelt';
        $isConfirmed = '';
        foreach ($part->getChangeEvents() as $event) {
            if ($event->getNewState() === Transaction::STATE_ACCEPTED) {
                $accepted = $event->getCreated();
                $isAccepted = 'active';
                if ($prefixed) {
                    $accepted = 'Akzeptiert am ' . $event->getCreated()->format('d.m.Y');
                }
            }
            if ($event->getNewState() === Transaction::STATE_CLEARED) {
                $cleared = $event->getCreated();
                $isCleared = 'active';
                if ($prefixed) {
                    $cleared = 'Bezahlt am ' . $event->getCreated()->format('d.m.Y');
                }
            }
            if ($event->getNewState() === Transaction::STATE_CONFIRMED) {
                $confirmed = $event->getCreated();
                $isConfirmed = 'active';
                if ($prefixed) {
                    $confirmed = 'Abgewickelt am ' . $event->getCreated()->format('d.m.Y');
                }
            }
        }

        return $this->environment->render(
            'extension/transaction_details.html.twig',
            [
                'created' => $created,
                'accepted' => $accepted,
                'cleared' => $cleared,
                'confirmed' => $confirmed,
                'isAccepted' => $isAccepted,
                'isCleared' => $isCleared,
                'isConfirmed' => $isConfirmed,
                'part' => $part,
                'variantClass' => $part->isDebtVariant() ? 'is-debt' : '',
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