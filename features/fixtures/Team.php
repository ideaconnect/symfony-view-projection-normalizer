<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;

/**
 * Test entity representing a team with multiple members.
 */
#[DefaultViewProjection(viewProjectionClass: TeamViewProjection::class)]
class Team implements NormalizableInterface
{
    /**
     * @param Person[] $members
     */
    public function __construct(
        private string $name,
        private string $department,
        private array $members,
        private ?Person $teamLead = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    /**
     * @return Person[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function getTeamLead(): ?Person
    {
        return $this->teamLead;
    }

    public function getMemberCount(): int
    {
        return count($this->members);
    }
}
