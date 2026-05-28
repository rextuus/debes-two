<?php

namespace App\EntityListener;

use App\Entity\Transaction;
use DateTime;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class TransactionEntityListener
{

    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function prePersist(Transaction $transaction, LifecycleEventArgs $event): void
    {
        $slug = $this->computeSlug($transaction);
        if ($slug) {
            $transaction->setSlug($slug);
        }
    }

    private function computeSlug(Transaction $transaction): ?string
    {
        $timeStamp = (new DateTime())->getTimestamp() + rand();
        if (!$transaction->getSlug() || '-' === $transaction->getSlug()) {
            return $this->slugger->slug(substr((string)$transaction, 0, 16))->lower() . $timeStamp;
        }
        return null;
    }

    public function preUpdate(Transaction $transaction, LifecycleEventArgs $event): void
    {
        $slug = $this->computeSlug($transaction);
        if ($slug) {
            $transaction->setSlug($slug);
        }
    }
}