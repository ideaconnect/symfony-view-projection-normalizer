<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit\Attribute;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \IDCT\Mvc\Attribute\DefaultViewProjection
 */
class DefaultViewProjectionTest extends TestCase
{
    public function testConstructorWithValidViewProjectionClass(): void
    {
        $attribute = new DefaultViewProjection(TestViewProjection::class);

        $this->assertSame(TestViewProjection::class, $attribute->getViewProjectionClass());
    }

    public function testConstructorWithNonExistentClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$viewProjectionClass class provided does not exist.');

        /* @phpstan-ignore-next-line Intentional invalid class string for constructor validation test. */
        new DefaultViewProjection('NonExistentClass');
    }

    public function testConstructorWithClassNotImplementingViewProjectionInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ViewProjection class must be an instance of ViewProjectionInterface.');

        /* @phpstan-ignore-next-line Intentional non-projection class for constructor validation test. */
        new DefaultViewProjection(stdClass::class);
    }

    public function testGetViewProjectionClass(): void
    {
        $attribute = new DefaultViewProjection(TestViewProjection::class);

        $this->assertSame(TestViewProjection::class, $attribute->getViewProjectionClass());
    }
}

/**
 * Test implementation of ViewProjectionInterface for testing purposes.
 */
class TestViewProjection implements ViewProjectionInterface
{
    public function __construct(private NormalizableInterface $data)
    {
    }

    public function getData(): NormalizableInterface
    {
        return $this->data;
    }
}
