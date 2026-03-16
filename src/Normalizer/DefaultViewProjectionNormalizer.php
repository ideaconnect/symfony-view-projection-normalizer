<?php

declare(strict_types=1);

namespace IDCT\Mvc\Normalizer;

use ArrayObject;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Proxy;
use IDCT\Mvc\Attribute\DefaultViewProjection;
use IDCT\Mvc\Model\NormalizableInterface;
use IDCT\Mvc\Model\ViewProjectionInterface;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Default View Projection Normalizer.
 *
 * This normalizer automatically converts entities/models marked with the DefaultViewProjection attribute
 * into their corresponding view projections before normalization. It acts as an intermediate layer
 * between your data models and the final serialized output.
 */
class DefaultViewProjectionNormalizer implements NormalizerInterface, NormalizerAwareInterface, ResetInterface
{
    use NormalizerAwareTrait;

    /** @var array<class-string, class-string<ViewProjectionInterface>> */
    private array $viewProjectionClassMap = [];

    /**
     * Normalizes an object by converting it to its configured view projection first.
     *
     * This method looks for the DefaultViewProjection attribute on the object's class,
     * instantiates the configured view projection with the original object, and then
     * delegates normalization to the next normalizer in the chain.
     *
     * @param mixed                $object  The object to normalize
     * @param string|null          $format  The format being normalized to
     * @param array<string, mixed> $context Additional context for normalization
     *
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<int|string, mixed>|null The normalized data
     *
     * @throws ExceptionInterface       If normalization fails
     * @throws InvalidArgumentException If no DefaultViewProjection attribute is found
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $class = $this->getObjectClass($object);
        $viewProjectionClass = $this->resolveViewProjectionClass($class);

        if (null === $viewProjectionClass) {
            throw new InvalidArgumentException("No DefaultViewProjection attribute found on class {$class}");
        }

        return $this->normalizer->normalize(new $viewProjectionClass($object), $format, $context);
    }

    /**
     * Determines if this normalizer can handle the given data.
     *
     * This normalizer supports objects that implement NormalizableInterface
     * and have the DefaultViewProjection attribute configured.
     *
     * @param mixed                $data    The data to check for normalization support
     * @param string|null          $format  The format being normalized to
     * @param array<string, mixed> $context Additional context for normalization
     *
     * @return bool True if this normalizer can handle the data, false otherwise
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!$data instanceof NormalizableInterface) {
            return false;
        }

        return null !== $this->resolveViewProjectionClass($this->getObjectClass($data));
    }

    /**
     * Gets the types supported by this normalizer.
     *
     * Returns an array indicating that this normalizer supports any class
     * implementing NormalizableInterface.
     *
     * @param string|null $format The format being normalized to
     *
     * @return array<string, bool> Array mapping supported types to boolean true
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }

    public function reset(): void
    {
        $this->viewProjectionClassMap = [];
    }

    /**
     * @return class-string
     */
    private function getObjectClass(object $object): string
    {
        return $object instanceof Proxy ? ClassUtils::getClass($object) : $object::class;
    }

    /**
     * @param class-string $class
     *
     * @return class-string<ViewProjectionInterface>|null
     */
    private function resolveViewProjectionClass(string $class): ?string
    {
        if (array_key_exists($class, $this->viewProjectionClassMap)) {
            return $this->viewProjectionClassMap[$class];
        }

        $reflector = new ReflectionClass($class);
        $attributes = $reflector->getAttributes(DefaultViewProjection::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var DefaultViewProjection $instance */
        $instance = $attributes[0]->newInstance();
        $this->viewProjectionClassMap[$class] = $instance->getViewProjectionClass();

        return $this->viewProjectionClassMap[$class];
    }
}
