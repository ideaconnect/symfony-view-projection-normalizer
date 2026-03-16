<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;

/**
 * Test entity representing a company with an owner (person).
 */
#[DefaultViewProjection(viewProjectionClass: CompanyViewProjection::class)]
class Company implements NormalizableInterface
{
    public function __construct(
        private string $name,
        private string $industry,
        private Person $owner,
        private int $foundedYear,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIndustry(): string
    {
        return $this->industry;
    }

    public function getOwner(): Person
    {
        return $this->owner;
    }

    public function getFoundedYear(): int
    {
        return $this->foundedYear;
    }
}
