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
    private bool $initial;

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
}