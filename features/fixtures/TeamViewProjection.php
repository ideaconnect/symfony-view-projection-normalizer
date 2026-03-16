<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * View projection for Team that shows team info and member collection.
 */
class TeamViewProjection implements ViewProjectionInterface
{
    private Team $team;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof Team) {
            throw new InvalidArgumentException('TeamViewProjection expects an instance of ' . Team::class . '.');
        }

        $this->team = $source;
    }

    #[SerializedName('teamName')]
    public function getName(): string
    {
        return $this->team->getName();
    }

    #[SerializedName('dept')]
    public function getDepartment(): string
    {
        return $this->team->getDepartment();
    }

    #[SerializedName('teamMembers')]
    public function getMembers(): array
    {
        return $this->team->getMembers();
    }

    #[SerializedName('lead')]
    public function getTeamLead(): ?Person
    {
        return $this->team->getTeamLead();
    }

    #[SerializedName('size')]
    public function getTeamSize(): int
    {
        return $this->team->getMemberCount();
    }

    public function getAverageAge(): float
    {
        $members = $this->team->getMembers();
        if (empty($members)) {
            return 0.0;
        }

        $totalAge = array_sum(array_map(fn ($member) => $member->getAge(), $members));

        return round($totalAge / count($members), 1);
    }
}
