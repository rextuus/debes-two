<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection\Form;

use App\Entity\GroupEvent;
use App\Entity\User;

class UserCollectionData
{
    /**
     * @var User[]
     */
    private array $users;

    private GroupEvent $groupEvent;

    private string $name;

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): UserCollectionData
    {
        $this->users = $users;
        return $this;
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function setInitial(bool $initial): UserCollectionData
    {
        $this->initial = $initial;
        return $this;
    }

    public function isAllOthers(): bool
    {
        return $this->allOthers;
    }

    public function setAllOthers(bool $allOthers): UserCollectionData
    {
        $this->allOthers = $allOthers;
        return $this;
    }

    public function getGroupEvent(): GroupEvent
    {
        return $this->groupEvent;
    }

    public function setGroupEvent(GroupEvent $groupEvent): UserCollectionData
    {
        $this->groupEvent = $groupEvent;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserCollectionData
    {
        $this->name = $name;
        return $this;
    }
}