<?php

declare(strict_types=1);

namespace App\Service\GroupEvent;

use App\Entity\User;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GroupEventInitData extends GroupEventData
{
    private User $creator;

    /**
     * @var User[]
     */
    private array $selectedUsers;

    /**
     * @var User[]
     */
    private array $allUsers;

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): GroupEventInitData
    {
        $this->creator = $creator;
        return $this;
    }

    public function getSelectedUsers(): array
    {
        return $this->selectedUsers;
    }

    public function setSelectedUsers(array $selectedUsers): GroupEventInitData
    {
        $this->selectedUsers = $selectedUsers;
        return $this;
    }

    public function getAllUsers(): array
    {
        return $this->allUsers;
    }

    public function setAllUsers(array $allUsers): GroupEventInitData
    {
        $this->allUsers = $allUsers;
        return $this;
    }
}