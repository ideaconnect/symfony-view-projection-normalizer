<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Behat;

use Behat\Behat\Context\Context;
use IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer;
use IDCT\Mvc\Tests\Behat\Fixtures\Company;
use IDCT\Mvc\Tests\Behat\Fixtures\Person;
use IDCT\Mvc\Tests\Behat\Fixtures\Team;
use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Behat context for testing normalization scenarios.
 */
class NormalizationContext implements Context
{
    private Serializer $serializer;
    private mixed $testObject;
    private array $normalizedResult;
    private string $serializedResult;

    public function __construct()
    {
        $this->initializeSerializer();
    }

    private function initializeSerializer(): void
    {
        // Configure metadata factory to read attributes
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        // Configure name converter to handle SerializedName attributes
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $normalizers = [
            new DefaultViewProjectionNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                $nameConverter,
                null,
                new ReflectionExtractor(),
                null,
                null,
                [
                    ObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    ObjectNormalizer::MAX_DEPTH_HANDLER => null,
                ],
            ),
        ];

        $encoders = [
            new JsonEncoder(),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @Given the serializer is properly configured with DefaultViewProjectionNormalizer
     */
    public function theSerializerIsProperlyConfiguredWithDefaultViewProjectionNormalizer(): void
    {
        // This is already done in the constructor, but this step documents it
        Assert::assertInstanceOf(Serializer::class, $this->serializer);
    }

    /**
     * @Given I have a person with first name :firstName and last name :lastName aged :age
     */
    public function iHaveAPersonWithFirstNameAndLastNameAged(string $firstName, string $lastName, int $age): void
    {
        $this->testObject = new Person($firstName, $lastName, $age);
    }

    /**
     * @Given I have a company :companyName in :industry founded in :year with owner :firstName :lastName aged :age
     */
    public function iHaveACompanyInIndustryFoundedInWithOwner(
        string $companyName,
        string $industry,
        int $year,
        string $firstName,
        string $lastName,
        int $age,
    ): void {
        $owner = new Person($firstName, $lastName, $age);
        $this->testObject = new Company($companyName, $industry, $owner, $year);
    }

    /**
     * @Given I have a team :teamName in :department with team lead :leadFirstName :leadLastName aged :leadAge
     */
    public function iHaveATeamInDepartmentWithTeamLead(
        string $teamName,
        string $department,
        string $leadFirstName,
        string $leadLastName,
        int $leadAge,
    ): void {
        $teamLead = new Person($leadFirstName, $leadLastName, $leadAge);
        $this->testObject = new Team($teamName, $department, [], $teamLead);
    }

    /**
     * @Given the team has member :firstName :lastName aged :age
     */
    public function theTeamHasMember(string $firstName, string $lastName, int $age): void
    {
        Assert::assertInstanceOf(Team::class, $this->testObject, 'Test object must be a Team');

        $member = new Person($firstName, $lastName, $age);
        $members = $this->testObject->getMembers();
        $members[] = $member;

        // Create a new team with the updated members list
        $this->testObject = new Team(
            $this->testObject->getName(),
            $this->testObject->getDepartment(),
            $members,
            $this->testObject->getTeamLead(),
        );
    }

    /**
     * @When I normalize the object
     */
    public function iNormalizeTheObject(): void
    {
        $this->normalizedResult = $this->serializer->normalize($this->testObject);
    }

    /**
     * @When I serialize the object to JSON
     */
    public function iSerializeTheObjectToJson(): void
    {
        $this->serializedResult = $this->serializer->serialize($this->testObject, 'json');
    }

    /**
     * @Then the normalized result should contain :key with value :value
     */
    public function theNormalizedResultShouldContainWithValue(string $key, string $value): void
    {
        Assert::assertArrayHasKey($key, $this->normalizedResult, "Key '{$key}' not found in normalized result");

        $actualValue = $this->normalizedResult[$key];
        if (is_numeric($value)) {
            $actualValue = (string) $actualValue;
        }

        Assert::assertEquals($value, $actualValue, "Expected '{$value}' but got '{$actualValue}' for key '{$key}'");
    }

    /**
     * @Then the normalized result should have :key as an object
     */
    public function theNormalizedResultShouldHaveAsAnObject(string $key): void
    {
        Assert::assertArrayHasKey($key, $this->normalizedResult, "Key '{$key}' not found in normalized result");
        Assert::assertIsArray($this->normalizedResult[$key], "Value for key '{$key}' should be an array/object");
    }

    /**
     * @Then the nested object :parentKey should contain :childKey with value :value
     */
    public function theNestedObjectShouldContainWithValue(string $parentKey, string $childKey, string $value): void
    {
        Assert::assertArrayHasKey($parentKey, $this->normalizedResult, "Parent key '{$parentKey}' not found");
        Assert::assertIsArray($this->normalizedResult[$parentKey], 'Parent value should be an array/object');
        Assert::assertArrayHasKey($childKey, $this->normalizedResult[$parentKey], "Child key '{$childKey}' not found in '{$parentKey}'");

        $actualValue = $this->normalizedResult[$parentKey][$childKey];
        if (is_numeric($value)) {
            $actualValue = (string) $actualValue;
        }

        Assert::assertEquals($value, $actualValue, "Expected '{$value}' but got '{$actualValue}' for nested key '{$parentKey}.{$childKey}'");
    }

    /**
     * @Then the normalized result should have :key as an array with :count items
     */
    public function theNormalizedResultShouldHaveAsAnArrayWithItems(string $key, int $count): void
    {
        Assert::assertArrayHasKey($key, $this->normalizedResult, "Key '{$key}' not found in normalized result");
        Assert::assertIsArray($this->normalizedResult[$key], "Value for key '{$key}' should be an array");
        Assert::assertCount($count, $this->normalizedResult[$key], "Expected {$count} items in array for key '{$key}'");
    }

    /**
     * @Then the array :arrayKey should have item :index containing :key with value :value
     */
    public function theArrayShouldHaveItemContainingWithValue(string $arrayKey, int $index, string $key, string $value): void
    {
        Assert::assertArrayHasKey($arrayKey, $this->normalizedResult, "Array key '{$arrayKey}' not found");
        Assert::assertIsArray($this->normalizedResult[$arrayKey], "Value for key '{$arrayKey}' should be an array");
        Assert::assertArrayHasKey($index, $this->normalizedResult[$arrayKey], "Index {$index} not found in array '{$arrayKey}'");
        Assert::assertIsArray($this->normalizedResult[$arrayKey][$index], "Item at index {$index} should be an array/object");
        Assert::assertArrayHasKey($key, $this->normalizedResult[$arrayKey][$index], "Key '{$key}' not found in array item");

        $actualValue = $this->normalizedResult[$arrayKey][$index][$key];
        if (is_numeric($value)) {
            $actualValue = (string) $actualValue;
        }

        Assert::assertEquals($value, $actualValue, "Expected '{$value}' but got '{$actualValue}' for array item key '{$key}'");
    }

    /**
     * @Then the serialized JSON should be valid
     */
    public function theSerializedJsonShouldBeValid(): void
    {
        $decoded = json_decode($this->serializedResult, true);
        Assert::assertNotNull($decoded, 'Serialized result should be valid JSON');
        Assert::assertEquals(JSON_ERROR_NONE, json_last_error(), 'JSON should be valid');
    }

    /**
     * @Then the JSON should contain :key with value :value
     */
    public function theJsonShouldContainWithValue(string $key, string $value): void
    {
        $decoded = json_decode($this->serializedResult, true);
        Assert::assertArrayHasKey($key, $decoded, "Key '{$key}' not found in JSON");

        $actualValue = $decoded[$key];
        if (is_numeric($value)) {
            $actualValue = (string) $actualValue;
        }

        Assert::assertEquals($value, $actualValue, "Expected '{$value}' but got '{$actualValue}' for JSON key '{$key}'");
    }
}
