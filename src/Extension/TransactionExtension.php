<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\TransactionStateChangeEvent;
use App\Extension\NextStateProvider\NextStateProvider;
use App\Service\Transaction\ChangeEvent\TransactionChangeEventService;
use App\Service\Transaction\TransactionDtos\TransactionDto;
use App\Service\Transaction\TransactionService;
use App\Service\Util\TimeConverter;
use Exception;
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
        private TransactionChangeEventService $changeEventService,
        private TransactionService $transactionService,
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
        $cardIcon = 'assets/img/create.svg';

        $stateProvider = $this->nextStateProvider->getHandlerForState($part);
        if (!$stateProvider){
            $message = sprintf(
                'No provider found for transaction in state: %s_%s',
                $part->getState(),
                $part->isDebtVariant() ? 'debt' : 'loan'
            );
            throw new Exception($message);
        }
        $contentCardParams = $stateProvider->getTwigParameters($part);

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

    public function renderTransactionPartSummary(TransactionDto $part, $exchangesElapsed = false): string
    {
        return $this->environment->render(
            'extension/transaction_part_summary.html.twig',
            [
                'dto' => $part,
                'debtVariant' => $part->isDebtVariant(),
                'exchangesElapsed' => $exchangesElapsed ? '' : 'hidden',
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
                    $accepted = 'Akzeptiert am ' . $event->getCreated()->format('d.m.Y H:m:s');
                }
            }
            if ($event->getNewState() === Transaction::STATE_CLEARED) {
                $cleared = $event->getCreated();
                $isCleared = 'active';
                if ($prefixed) {
                    $cleared = 'Bezahlt am ' . $event->getCreated()->format('d.m.Y H:m:s');
                }
            }
            if ($event->getNewState() === Transaction::STATE_CONFIRMED) {
                $confirmed = $event->getCreated();
                $isConfirmed = 'active';
                if ($prefixed) {
                    $confirmed = 'Abgewickelt am ' . $event->getCreated()->format('d.m.Y H:m:s');
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