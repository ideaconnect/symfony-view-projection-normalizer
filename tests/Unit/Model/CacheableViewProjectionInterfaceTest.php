<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit\Model;

use IDCT\Mvc\Model\CacheableViewProjectionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CacheableViewProjectionInterface
 * @coversNothing
 */
class CacheableViewProjectionInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(CacheableViewProjectionInterface::class));
    }

    public function testExtendsNormalizerInterface(): void
    {
        $reflection = new \ReflectionClass(CacheableViewProjectionInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        $this->assertContains(NormalizerInterface::class, $interfaces);
    }

    public function testCanBeImplemented(): void
    {
        $implementation = new class implements CacheableViewProjectionInterface {
            public function getCacheKey(): string
            {
                return 'test-cache-key';
            }

            /**
             * @param array<string, mixed> $context
             * @return array<string, mixed>|string|int|float|bool|\ArrayObject<int|string, mixed>|null
             */
            public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
            {
                if ($object instanceof \ArrayObject) {
                    return $object;
                }

                if ($object === null) {
                    return null;
                }

                if (is_bool($object)) {
                    return $object;
                }

                if (is_int($object)) {
                    return $object;
                }

                if (is_float($object)) {
                    return $object;
                }

                if (is_string($object)) {
                    return $object;
                }

                return ['test' => 'data'];
            }

            public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['*' => false];
            }
        };

        $this->assertInstanceOf(CacheableViewProjectionInterface::class, $implementation);
        $this->assertSame('test-cache-key', $implementation->getCacheKey());
    }

    public function testGetCacheKeyMethod(): void
    {
        $reflection = new \ReflectionClass(CacheableViewProjectionInterface::class);

        $this->assertTrue($reflection->hasMethod('getCacheKey'));

        $method = $reflection->getMethod('getCacheKey');
        $this->assertTrue($method->isPublic());
        $this->assertSame('string', (string) $method->getReturnType());
    }
}
