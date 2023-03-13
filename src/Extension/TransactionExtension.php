<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\TransactionStateChangeEvent;
use App\Service\Transaction\TransactionDtos\TransactionDto;
use App\Service\Util\TimeConverter;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class TransactionExtension extends AbstractExtension
{
    public function __construct(private Environment $environment, private TimeConverter $timeConverter)
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
        $acceptButton = 'Bezahlen';
        $declineButton = 'Abgeben';
        $acceptLink = 'transaction_process';
        $declineLink = 'transaction_process';

        switch ($part->getState()){
            case 2:
                $declineLink = '';
                break;
            case 3:
                $acceptLink = '';
                $declineButton = 'Hinweis senden';
                break;
        }

        return $this->environment->render(
            'extension/transaction_part_card.html.twig',
            [
                'part' => $part,
                'ago'  => $this->timeConverter->getUserFriendlyDateTime($part->getEdited()),
                'acceptButton' => $acceptButton,
                'acceptLink' => $acceptLink,
                'slug' => $part->getTransactionSlug(),
                'declineButton' => $declineButton,
                'declineLink' => $declineLink,
            ]
        );
    }

    public function renderTransactionPartSummary(TransactionDto $part): string
    {
        return $this->environment->render(
            'extension/transaction_part_summary.html.twig',
            [
                'dto'         => $part,
                'debtVariant' => $part->isDebtVariant(),
            ]
        );
    }

    public function renderTransactionPartDetails(TransactionDto $part, bool $prefixed = false): string
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
                'created'     => $created,
                'accepted'    => $accepted,
                'cleared'     => $cleared,
                'confirmed'   => $confirmed,
                'isAccepted'  => $isAccepted,
                'isCleared'   => $isCleared,
                'isConfirmed' => $isConfirmed,
                'part'        => $part,
            ]
        );
    }
}