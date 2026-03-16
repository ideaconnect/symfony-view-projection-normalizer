<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;

/**
 * Test entity representing a person with first and last name
 */
#[DefaultViewProjection(viewProjectionClass: PersonViewProjection::class)]
class Person implements NormalizableInterface
{
    public function __construct(
        private string $firstName,
        private string $lastName,
        private int $age
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
