<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit\Normalizer;

use Doctrine\Common\Proxy\Proxy;
use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @covers \IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer
 * @uses \IDCT\Mvc\Attribute\DefaultViewProjection
 */
class DefaultViewProjectionNormalizerTest extends TestCase
{
    private DefaultViewProjectionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DefaultViewProjectionNormalizer();
        $this->normalizer->setNormalizer($this->createStub(NormalizerInterface::class));
    }

    public function testNormalizeWithValidObject(): void
    {
        $testEntity = new TestEntity();
        $expectedResult = ['normalized' => 'data'];

        /** @var NormalizerInterface&MockObject $mockNormalizer */
        $mockNormalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->setNormalizer($mockNormalizer);

        $mockNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($this->isInstanceOf(TestEntityViewProjection::class))
            ->willReturn($expectedResult);

        $result = $this->normalizer->normalize($testEntity);

        $this->assertSame($expectedResult, $result);
    }

    public function testNormalizeWithProxyObject(): void
    {
        $testProxy = new TestEntityProxy();
        $expectedResult = ['normalized' => 'proxy_data'];

        /** @var NormalizerInterface&MockObject $mockNormalizer */
        $mockNormalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->setNormalizer($mockNormalizer);

        $mockNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($this->isInstanceOf(TestEntityViewProjection::class))
            ->willReturn($expectedResult);

        $result = $this->normalizer->normalize($testProxy);

        $this->assertSame($expectedResult, $result);
    }

    public function testNormalizeWithObjectWithoutAttribute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No DefaultViewProjection attribute found on class');

        $objectWithoutAttribute = new class implements NormalizableInterface {};
        $this->normalizer->normalize($objectWithoutAttribute);
    }

    public function testSupportsNormalizationWithValidObject(): void
    {
        $testEntity = new TestEntity();

        $this->assertTrue($this->normalizer->supportsNormalization($testEntity));
    }

    public function testSupportsNormalizationWithNonNormalizableObject(): void
    {
        $nonNormalizableObject = new \stdClass();

        $this->assertFalse($this->normalizer->supportsNormalization($nonNormalizableObject));
    }

    public function testSupportsNormalizationWithObjectWithoutAttribute(): void
    {
        $objectWithoutAttribute = new class implements NormalizableInterface {};

        $this->assertFalse($this->normalizer->supportsNormalization($objectWithoutAttribute));
    }

    public function testSupportsNormalizationWithProxy(): void
    {
        $testProxy = new TestEntityProxy();

        $this->assertTrue($this->normalizer->supportsNormalization($testProxy));
    }

    public function testGetSupportedTypes(): void
    {
        $result = $this->normalizer->getSupportedTypes('json');

        $expected = [
            NormalizableInterface::class => true,
        ];

        $this->assertSame($expected, $result);
    }

    public function testGetSupportedTypesWithNullFormat(): void
    {
        $result = $this->normalizer->getSupportedTypes(null);

        $expected = [
            NormalizableInterface::class => true,
        ];

        $this->assertSame($expected, $result);
    }

    public function testNormalizeWithDifferentFormats(): void
    {
        $testEntity = new TestEntity();
        $expectedResult = ['normalized' => 'data'];

        /** @var NormalizerInterface&MockObject $mockNormalizer */
        $mockNormalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->setNormalizer($mockNormalizer);

        $mockNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with(
                $this->isInstanceOf(TestEntityViewProjection::class),
                'xml',
                ['custom' => 'context']
            )
            ->willReturn($expectedResult);

        $result = $this->normalizer->normalize($testEntity, 'xml', ['custom' => 'context']);

        $this->assertSame($expectedResult, $result);
    }

    public function testSupportsNormalizationWithDifferentFormats(): void
    {
        $testEntity = new TestEntity();

        $this->assertTrue($this->normalizer->supportsNormalization($testEntity, 'json'));
        $this->assertTrue($this->normalizer->supportsNormalization($testEntity, 'xml'));
        $this->assertTrue($this->normalizer->supportsNormalization($testEntity, null));
    }

    public function testSupportsNormalizationCachesResolvedProjectionClass(): void
    {
        $testEntity = new TestEntity();

        $this->assertTrue($this->normalizer->supportsNormalization($testEntity));
        $this->assertTrue($this->normalizer->supportsNormalization($testEntity));

        $cache = $this->getViewProjectionClassMap();

        $this->assertArrayHasKey(TestEntity::class, $cache);
        $this->assertSame(TestEntityViewProjection::class, $cache[TestEntity::class]);
    }

    public function testSupportsNormalizationDoesNotCacheMissingProjectionMetadata(): void
    {
        $objectWithoutAttribute = new class implements NormalizableInterface {};

        $this->assertFalse($this->normalizer->supportsNormalization($objectWithoutAttribute));
        $this->assertFalse($this->normalizer->supportsNormalization($objectWithoutAttribute));

        $this->assertArrayNotHasKey($objectWithoutAttribute::class, $this->getViewProjectionClassMap());
    }

    public function testImplementsResetInterface(): void
    {
        $this->assertInstanceOf(ResetInterface::class, $this->normalizer);
    }

    public function testResetClearsResolvedProjectionCache(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new TestEntity()));
        $this->assertNotEmpty($this->getViewProjectionClassMap());

        $this->normalizer->reset();

        $this->assertSame([], $this->getViewProjectionClassMap());
    }

    /**
     * @return array<class-string, class-string>
     */
    private function getViewProjectionClassMap(): array
    {
        $reflection = new \ReflectionClass($this->normalizer);
        $property = $reflection->getProperty('viewProjectionClassMap');

        return $property->getValue($this->normalizer);
    }
}

/**
 * Test entity for testing purposes
 */
#[DefaultViewProjection(viewProjectionClass: TestEntityViewProjection::class)]
class TestEntity implements NormalizableInterface
{
    public function __construct(
        public string $name = 'Test Entity',
        public int $id = 1
    ) {
    }
}

/**
 * Test view projection for testing purposes
 */
class TestEntityViewProjection implements ViewProjectionInterface
{
    private TestEntity $entity;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof TestEntity) {
            throw new \InvalidArgumentException('TestEntityViewProjection expects an instance of ' . TestEntity::class . '.');
        }

        $this->entity = $source;
    }

    public function getName(): string
    {
        return $this->entity->name;
    }

    public function getId(): int
    {
        return $this->entity->id;
    }
}

/**
 * Test proxy class that simulates Doctrine proxy behavior
 */
/**
 * @implements Proxy<TestEntity>
 */
#[DefaultViewProjection(viewProjectionClass: TestEntityViewProjection::class)]
class TestEntityProxy extends TestEntity implements Proxy
{
    public function __load(): void
    {
        // Simulate proxy loading
    }

    public function __isInitialized(): bool
    {
        return true;
    }

    public function __setInitialized($initialized): void
    {
        // Simulate setting initialization state
    }

    public function __setInitializer(?\Closure $initializer = null): void
    {
        // Simulate setting initializer
    }

    public function __getInitializer(): ?\Closure
    {
        return null;
    }

    public function __setCloner(?\Closure $cloner = null): void
    {
        // Simulate setting cloner
    }

    public function __getCloner(): ?\Closure
    {
        return null;
    }

    public function __getLazyProperties(): array
    {
        return [];
    }
}
