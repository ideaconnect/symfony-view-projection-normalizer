<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Unit\Model;

use IDCT\Mvc\Model\NormalizableInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for NormalizableInterface
 * @coversNothing
 */
class NormalizableInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(NormalizableInterface::class));
    }

    public function testCanBeImplemented(): void
    {
        $implementation = new class implements NormalizableInterface {};

        $this->assertInstanceOf(NormalizableInterface::class, $implementation);
    }
}
