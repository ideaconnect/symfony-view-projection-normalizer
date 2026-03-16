<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * View projection for Person that combines firstName and lastName into a single name field.
 */
class PersonViewProjection implements ViewProjectionInterface
{
    private Person $person;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof Person) {
            throw new InvalidArgumentException('PersonViewProjection expects an instance of ' . Person::class . '.');
        }

        $this->person = $source;
    }

    #[SerializedName('n')]
    public function getName(): string
    {
        return $this->person->getFirstName() . ' ' . $this->person->getLastName();
    }

    #[SerializedName('a')]
    public function getAge(): int
    {
        return $this->person->getAge();
    }
}
