MVC View Projection Normalizer
=========================

![Tests](https://github.com/ideaconnect/symfony-view-projection-normalizer/actions/workflows/tests.yml/badge.svg)
![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)
![Symfony](https://img.shields.io/badge/symfony-7%20%7C%208-blue)
![Tests](https://img.shields.io/badge/tests-PHPUnit%20%2B%20Behat-blue)

This package adds the `DefaultViewProjection` attribute and a Symfony Serializer normalizer that turns marked entities into dedicated view projection objects before serialization.

Instead of pushing response-shaping logic into entities or growing large serializer-group configurations, you define an explicit read model for each entity and let the serializer use that model automatically.

## Why Use It

Use this library when your serialized output is not a 1:1 copy of your entity structure.

- **Keep entities focused**: domain objects stay free of presentation-specific getters and aliases
- **Model the response explicitly**: ViewProjections become small read models for the view or API layer
- **Handle derived fields cleanly**: combine fields, rename them, and expose calculated values without serializer-group sprawl
- **Improve cacheability**: caching a stable projection output is usually easier than caching raw entities with ad hoc serialization rules

In practice, this sits between your domain model and your serialized response. The entity remains the source of truth; the ViewProjection defines how that entity should look when returned to the client.

## ✨ Features

- **Attribute-based Configuration**: Use `#[DefaultViewProjection]` to specify ViewProjections
- **SerializedName Support**: Full support for `#[SerializedName('alias')]` attributes
- **Nested Object Handling**: Automatic handling of nested ViewProjections
- **Collection Support**: Transform arrays/collections of objects
- **Resettable Metadata Cache**: Resolved projection classes are cached and can be cleared through Symfony's `ResetInterface`
- **100% Test Coverage**: Comprehensive PHPUnit + Behat test coverage
- **CI/CD Ready**: Complete GitHub Actions workflows

## How It Works

1. Your source class implements `NormalizableInterface`.
2. You attach `#[DefaultViewProjection(...)]` to that class.
3. The normalizer resolves the configured projection class.
4. The source object is wrapped in the projection and normalization continues through Symfony Serializer.

## Where It Fits

This library is a good fit when you want a lightweight projection layer on top of Symfony Serializer.

- **Better than serializer groups for reshaping output**: especially when output fields are computed, combined, or nested differently from the entity graph
- **Useful for MVC and API responses**: one entity can expose a compact response model without polluting the entity itself
- **Helpful for nested object graphs**: nested entities with their own projections are normalized consistently

It is intentionally simple: each entity points to one default projection class. That keeps the integration small and predictable.

## Tradeoffs

- **One default projection per entity**: this package does not currently choose projections dynamically by context, role, or API version
- **Runtime reflection and instantiation**: projection metadata is resolved during normalization
- **Explicit opt-in**: entities must implement `NormalizableInterface` and projections must implement `ViewProjectionInterface`
- **Positive-only cache**: only successful projection resolutions are cached; unsupported classes are re-checked when encountered again

Those tradeoffs are reasonable if your goal is a thin, explicit read-model layer rather than a full projection-selection framework.

## 🚀 Quick Start

### Installation

```bash
composer require idct/symfony-view-projection-normalizer
```

### Symfony Configuration

Register the normalizer in your `services.yaml`:

```yaml
  IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer:
    tags:
      - { name: serializer.normalizer, priority: 100 }
```

The attribute is not enough on its own. A class must implement `NormalizableInterface` to be considered by the normalizer.

If you run Symfony in a long-lived process, the normalizer also implements `ResetInterface`, so its resolved-projection cache can be cleared between jobs.

### Minimal Example

Add a source class:

```php
use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;

#[DefaultViewProjection(viewProjectionClass: UserViewProjection::class)]
class User implements NormalizableInterface
{
    public function __construct(
        private string $firstName,
        private string $lastName,
        private int $age
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
```

Create a projection class:

```php
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

class UserViewProjection implements ViewProjectionInterface
{
    private User $user;

    public function __construct(NormalizableInterface $source)
    {
        if (!$source instanceof User) {
            throw new \InvalidArgumentException('UserViewProjection expects an instance of ' . User::class . '.');
        }

        $this->user = $source;
    }

    #[SerializedName('n')]
    public function getName(): string
    {
        return $this->user->getFirstName() . ' ' . $this->user->getLastName();
    }

    #[SerializedName('a')]
    public function getAge(): int
    {
        return $this->user->getAge();
    }
}
```

When Symfony Serializer normalizes a `User`, the normalizer will first wrap it in `UserViewProjection` and then serialize the projection output.

### Constructor Contract

`ViewProjectionInterface` requires a constructor that accepts `NormalizableInterface`.

That is the minimum interface contract only. It does not prove that a projection matches the exact source class configured by `DefaultViewProjection`. In practice, projection classes should narrow the incoming source object immediately, usually with an `instanceof` guard as shown above.

**Result:**
```json
{
  "n": "John Doe",
  "a": 30
}
```

### Manual Serializer Setup

If you are not wiring this through Symfony services, add the normalizer to your serializer stack before the default object normalizer.

```php
use IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer([
    new DefaultViewProjectionNormalizer(),
    new ObjectNormalizer(),
]);
```

## Common Use Cases

- Return a compact API response without exposing the full entity structure
- Rename fields with `#[SerializedName]` while keeping entity method names domain-oriented
- Add calculated values such as aggregates, labels, or formatted names
- Normalize nested entities and collections through their own projections
- Create cache-friendly response models for controllers and view layers

## Runtime Notes

- The normalizer caches only successful `DefaultViewProjection` resolutions in memory.
- Classes without the attribute are not retained in the cache.
- Calling `reset()` clears the resolved projection map, which is useful in long-running workers.
- Each source class points to one default projection class.

## 🧪 Testing

### Run All Tests
```bash
composer run test:unit && composer run test:feature
```

### Individual Test Suites
```bash
# Static analysis
composer run test:static

# PHPUnit unit tests
composer run test:unit

# Behat acceptance tests
composer run test:feature

# PHPUnit with coverage reports
composer run test:coverage

# Coverage verification
php bin/check-coverage.php
```

## 📚 Documentation

- **[Behat Tests](features/README.md)**: Acceptance test documentation
- **[Coverage Reports](coverage/html/)**: Detailed coverage analysis

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Run checks: `composer run test:static && composer run test:unit && composer run test:feature`
4. Ensure 100% coverage
5. Submit a pull request

# 💖 Love my work? Support it! 🚀

* 🪙 **BTC**: bc1qntms755swm3nplsjpllvx92u8wdzrvs474a0hr
* 💎 **ETH**: 0x08E27250c91540911eD27F161572aFA53Ca24C0a
* ⚡ **TRX**: TVXWaU4ScNV9RBYX5RqFmySuB4zF991QaE
* 🚀 **LTC**: LN5ApP1Yhk4iU9Bo1tLU8eHX39zDzzyZxB
* ☕ **Buy me a coffee**: https://buymeacoffee.com/idct
* 💝 **Sponsor**: https://github.com/sponsors/ideaconnect

## 📄 License

This project is licensed under the MIT License.