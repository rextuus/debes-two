<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\UserCollection;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UserCollectionDto
{
    private string $name;
    private string $colorClass;
    private int $id;
    private string $members;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserCollectionDto
    {
        $this->name = $name;
        return $this;
    }

    public function getColorClass(): string
    {
        return $this->colorClass;
    }

    public function setColorClass(string $colorClass): UserCollectionDto
    {
        $this->colorClass = $colorClass;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UserCollectionDto
    {
        $this->id = $id;
        return $this;
    }

    public function getMembers(): string
    {
        return $this->members;
    }

    public function setMembers(string $members): UserCollectionDto
    {
        $this->members = $members;
        return $this;
    }
}
