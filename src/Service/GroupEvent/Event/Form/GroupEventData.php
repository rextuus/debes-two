<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Event\Form;

use App\Entity\GroupEvent;
use DateTimeInterface;

class GroupEventData
{
    private string $description;

    private bool $open;
    private DateTimeInterface|null $evaluated;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): GroupEventData
    {
        $this->description = $description;
        return $this;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): GroupEventData
    {
        $this->open = $open;
        return $this;
    }

    public function getEvaluated(): ?DateTimeInterface
    {
        return $this->evaluated;
    }

    public function setEvaluated(?DateTimeInterface $evaluated): GroupEventData
    {
        $this->evaluated = $evaluated;
        return $this;
    }

    public function initFrom(GroupEvent $event): GroupEventData
    {
        $this->setDescription($event->getDescription());
        $this->setEvaluated($event->getEvaluated());
        $this->setOpen($event->isOpen());

        return $this;
    }
}