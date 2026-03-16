<?php

declare(strict_types=1);

namespace IDCT\Mvc\Tests\Integration;

use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;

/**
 * @covers \IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer
 * @covers \IDCT\Mvc\Attribute\DefaultViewProjection
 */
class NormalizerIntegrationTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $normalizers = [
            new DefaultViewProjectionNormalizer(),
            new ObjectNormalizer(),
        ];

        $encoders = [
            new JsonEncoder(),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testFullNormalizationFlow(): void
    {
        $user = new IntegrationTestUser('John Doe', 'john@example.com', 25);

        $result = $this->serializer->normalize($user);

        $expected = [
            'displayName' => 'John Doe',
            'contactEmail' => 'john@example.com',
            'adult' => true
        ];

        $this->assertSame($expected, $result);
    }

    public function testSerializationFlow(): void
    {
        $user = new IntegrationTestUser('Jane Smith', 'jane@example.com', 17);

        $result = $this->serializer->serialize($user, 'json');

        $expected = json_encode([
            'displayName' => 'Jane Smith',
            'contactEmail' => 'jane@example.com',
            'adult' => false
        ]);

        $this->assertSame($expected, $result);
    }

    public function testComplexObjectNormalization(): void
    {
        $product = new IntegrationTestProduct('Laptop', 999.99, true);

        $result = $this->serializer->normalize($product);

        $expected = [
            'name' => 'Laptop',
            'formattedPrice' => '$999.99',
            'available' => true,
            'category' => 'Electronics'
        ];

        $this->assertSame($expected, $result);
    }
}

/**
 * Test user entity for integration testing
 */
#[DefaultViewProjection(viewProjectionClass: IntegrationTestUserViewProjection::class)]
class IntegrationTestUser implements NormalizableInterface
{
    public function __construct(
        private string $name,
        private string $email,
        private int $age
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}

/**
 * Test user view projection for integration testing
 */
class IntegrationTestUserViewProjection implements ViewProjectionInterface
{
    private IntegrationTestUser $user;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof IntegrationTestUser) {
            throw new \InvalidArgumentException('IntegrationTestUserViewProjection expects an instance of ' . IntegrationTestUser::class . '.');
        }

        $this->user = $source;
    }

    public function getDisplayName(): string
    {
        return $this->user->getName();
    }

    public function getContactEmail(): string
    {
        return $this->user->getEmail();
    }

    public function isAdult(): bool
    {
        return $this->user->getAge() >= 18;
    }
}

/**
 * Test product entity for integration testing
 */
#[DefaultViewProjection(viewProjectionClass: IntegrationTestProductViewProjection::class)]
class IntegrationTestProduct implements NormalizableInterface
{
    public function __construct(
        private string $name,
        private float $price,
        private bool $inStock
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }
}

/**
 * Test product view projection for integration testing
 */
class IntegrationTestProductViewProjection implements ViewProjectionInterface
{
    private IntegrationTestProduct $product;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof IntegrationTestProduct) {
            throw new \InvalidArgumentException('IntegrationTestProductViewProjection expects an instance of ' . IntegrationTestProduct::class . '.');
        }

        $this->product = $source;
    }

    public function getName(): string
    {
        return $this->product->getName();
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->product->getPrice(), 2);
    }

    public function isAvailable(): bool
    {
        return $this->product->isInStock();
    }

    public function getCategory(): string
    {
        return 'Electronics';
    }
}
