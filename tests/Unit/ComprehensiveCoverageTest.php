<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/**
 * Comprehensive test for edge cases and complete coverage.
 *
 * @covers \IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer
 * @covers \IDCT\Mvc\Attribute\DefaultViewProjection
 */
class ComprehensiveCoverageTest extends TestCase
{
    public function testDefaultViewProjectionAttributeWithEdgeCases(): void
    {
        // Test with fully qualified class name
        $attribute = new DefaultViewProjection(TestViewProjectionForCoverage::class);
        $this->assertSame(TestViewProjectionForCoverage::class, $attribute->getViewProjectionClass());

        // Test constructor validation paths are fully exercised
        $this->assertTrue(class_exists($attribute->getViewProjectionClass()));

        $reflection = new ReflectionClass($attribute->getViewProjectionClass());
        $interfaces = $reflection->getInterfaceNames();
        $this->assertContains(ViewProjectionInterface::class, $interfaces);
    }

    public function testNormalizerWithMissingAttribute(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();
        $stubNormalizer = $this->createStub(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $normalizer->setNormalizer($stubNormalizer);

        $entityWithoutAttribute = new class implements NormalizableInterface {
            public string $data = 'test';
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No DefaultViewProjection attribute found on class');

        $normalizer->normalize($entityWithoutAttribute);
    }

    public function testNormalizerWithEmptyAttributesArray(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();

        // This should trigger the empty attributes check in normalize method
        $objectWithoutAttribute = new class implements NormalizableInterface {};

        $this->expectException(InvalidArgumentException::class);
        $normalizer->normalize($objectWithoutAttribute);
    }

    public function testGetSupportedTypesMethodCoverage(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();

        // Test with different format values to ensure complete coverage
        $jsonTypes = $normalizer->getSupportedTypes('json');
        $xmlTypes = $normalizer->getSupportedTypes('xml');
        $nullTypes = $normalizer->getSupportedTypes(null);

        $expectedTypes = [NormalizableInterface::class => true];

        $this->assertSame($expectedTypes, $jsonTypes);
        $this->assertSame($expectedTypes, $xmlTypes);
        $this->assertSame($expectedTypes, $nullTypes);
    }

    public function testSupportsNormalizationMethodCoverage(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();

        // Test all branches of supportsNormalization

        // 1. Non-NormalizableInterface object
        $stdObject = new stdClass();
        $this->assertFalse($normalizer->supportsNormalization($stdObject));

        // 2. NormalizableInterface but no attribute
        $noAttributeObject = new class implements NormalizableInterface {};
        $this->assertFalse($normalizer->supportsNormalization($noAttributeObject));

        // 3. NormalizableInterface with attribute
        $withAttributeObject = new TestEntityForCoverage();
        $this->assertTrue($normalizer->supportsNormalization($withAttributeObject));

        // Test with different formats and contexts
        $this->assertTrue($normalizer->supportsNormalization($withAttributeObject, 'json', []));
        $this->assertTrue($normalizer->supportsNormalization($withAttributeObject, 'xml', ['groups' => ['test']]));
        $this->assertTrue($normalizer->supportsNormalization($withAttributeObject, null, ['custom' => 'context']));
    }

    public function testNormalizeMethodWithDifferentContexts(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();
        $mockNormalizer = $this->createMock(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $normalizer->setNormalizer($mockNormalizer);

        $testEntity = new TestEntityForCoverage();

        $mockNormalizer
            ->expects($this->exactly(3))
            ->method('normalize')
            ->willReturn(['test' => 'data']);

        // Test normalize with different parameter combinations
        $result1 = $normalizer->normalize($testEntity);
        $result2 = $normalizer->normalize($testEntity, 'json');
        $result3 = $normalizer->normalize($testEntity, 'xml', ['custom' => 'context']);

        $this->assertSame(['test' => 'data'], $result1);
        $this->assertSame(['test' => 'data'], $result2);
        $this->assertSame(['test' => 'data'], $result3);
    }

    public function testProxyHandlingBranches(): void
    {
        $normalizer = new DefaultViewProjectionNormalizer();
        $mockNormalizer = $this->createMock(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $normalizer->setNormalizer($mockNormalizer);

        // Test with regular object (non-proxy)
        $regularEntity = new TestEntityForCoverage();

        $mockNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->willReturn(['regular' => 'entity']);

        $result = $normalizer->normalize($regularEntity);
        $this->assertSame(['regular' => 'entity'], $result);

        // Test supportsNormalization with regular object
        $this->assertTrue($normalizer->supportsNormalization($regularEntity));
    }

    public function testAttributeConstructorValidationBranches(): void
    {
        // Test successful construction
        $validAttribute = new DefaultViewProjection(TestViewProjectionForCoverage::class);
        $this->assertInstanceOf(DefaultViewProjection::class, $validAttribute);

        // Test class existence validation
        try {
            /* @phpstan-ignore-next-line Intentional invalid class string for constructor validation coverage. */
            new DefaultViewProjection('CompletelyNonExistentClassName123');
            $this->fail('Expected InvalidArgumentException for non-existent class');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('$viewProjectionClass class provided does not exist.', $e->getMessage());
        }

        // Test ViewProjectionInterface implementation validation
        try {
            /* @phpstan-ignore-next-line Intentional non-projection class for constructor validation coverage. */
            new DefaultViewProjection(stdClass::class);
            $this->fail('Expected InvalidArgumentException for class not implementing ViewProjectionInterface');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('ViewProjection class must be an instance of ViewProjectionInterface.', $e->getMessage());
        }
    }
}

#[DefaultViewProjection(viewProjectionClass: TestViewProjectionForCoverage::class)]
class TestEntityForCoverage implements NormalizableInterface
{
    public function __construct(
        public string $name = 'Test Entity Coverage',
        public int $value = 42,
    ) {
    }
}

class TestViewProjectionForCoverage implements ViewProjectionInterface
{
    private TestEntityForCoverage $entity;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof TestEntityForCoverage) {
            throw new InvalidArgumentException('TestViewProjectionForCoverage expects an instance of ' . TestEntityForCoverage::class . '.');
        }

        $this->entity = $source;
    }

    public function getName(): string
    {
        return $this->entity->name;
    }

    public function getValue(): int
    {
        return $this->entity->value;
    }
}
