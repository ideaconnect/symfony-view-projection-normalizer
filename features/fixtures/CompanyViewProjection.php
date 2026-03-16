<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat\Fixtures;

use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * View projection for Company that shows business info and owner details.
 */
class CompanyViewProjection implements ViewProjectionInterface
{
    private Company $company;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof Company) {
            throw new InvalidArgumentException('CompanyViewProjection expects an instance of ' . Company::class . '.');
        }

        $this->company = $source;
    }

    #[SerializedName('companyName')]
    public function getName(): string
    {
        return $this->company->getName();
    }

    #[SerializedName('sector')]
    public function getIndustry(): string
    {
        return $this->company->getIndustry();
    }

    #[SerializedName('ownerInfo')]
    public function getOwner(): Person
    {
        return $this->company->getOwner();
    }

    public function getYearsInBusiness(): int
    {
        return date('Y') - $this->company->getFoundedYear();
    }
}
