<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventUserCollectionData
{
    /**
     * @var User[]
     */
    private array $users;
    private bool $initial = false;
    private bool $allOthers = false;

    private GroupEvent $groupEvent;

    private string $name;

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): GroupEventUserCollectionData
    {
        $this->users = $users;
        return $this;
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function setInitial(bool $initial): GroupEventUserCollectionData
    {
        $this->initial = $initial;
        return $this;
    }

    public function isAllOthers(): bool
    {
        return $this->allOthers;
    }

    public function setAllOthers(bool $allOthers): GroupEventUserCollectionData
    {
        $this->allOthers = $allOthers;
        return $this;
    }

    public function getGroupEvent(): GroupEvent
    {
        return $this->groupEvent;
    }

    public function setGroupEvent(GroupEvent $groupEvent): GroupEventUserCollectionData
    {
        $this->groupEvent = $groupEvent;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GroupEventUserCollectionData
    {
        $this->name = $name;
        return $this;
    }
}