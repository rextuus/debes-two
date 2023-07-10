<?php

namespace App\Service\Mailer;

use App\Entity\Transaction;
use App\Entity\User;

interface MailTemplateInterface
{
    public function getName(): string;
    public function getReceiver(): User;
    public function getSender(): User;
    public function getHeader(): string;
    public function getHeaderImageSrc(): string;
    public function getInteractor(): string;
    public function getInteractorVariant(): string;
    public function getSubject(): string;
    public function getText(): string;
    public function getHandleLink(): ?string;
    public function setTransaction(Transaction $transaction);

    public function getDebts(): float;
    public function getDetailText(): ?string;
}