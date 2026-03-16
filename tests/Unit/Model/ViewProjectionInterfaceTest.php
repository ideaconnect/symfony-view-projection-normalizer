<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit\Model;

use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ViewProjectionInterface.
 *
 * @coversNothing
 */
class ViewProjectionInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(ViewProjectionInterface::class));
    }

    public function testCanBeImplemented(): void
    {
        $source = new class implements NormalizableInterface {};
        $implementation = new class($source) implements ViewProjectionInterface {
            public function __construct(NormalizableInterface $source)
            {
            }
        };

        $this->assertInstanceOf(ViewProjectionInterface::class, $implementation);
    }
}
