<?php

namespace IDCT\Mvc\Attribute;

use InvalidArgumentException;
use IDCT\Mvc\Model\ViewProjectionInterface;
use Attribute;

/**
 * Default View Projection Attribute
 *
 * This attribute is used to specify which view projection class should be used
 * when normalizing an entity or model. When applied to a class that implements
 * NormalizableInterface, the DefaultViewProjectionNormalizer will automatically
 * convert instances to the specified view projection before normalization.
 *
 * Example usage:
 * ```php
 * #[DefaultViewProjection(viewProjectionClass: UserViewProjection::class)]
 * class User implements NormalizableInterface
 * {
 *     // ... class implementation
 * }
 * ```
 *
 * @package IDCT\Mvc\Attribute
 */
#[Attribute]
class DefaultViewProjection
{
    /**
     * Constructor for DefaultViewProjection attribute.
     *
    * Validates that the provided view projection class exists and implements ViewProjectionInterface.
     *
    * @param class-string<ViewProjectionInterface> $viewProjectionClass The fully qualified class name of the view projection to use
     * @throws InvalidArgumentException If the class doesn't exist or doesn't implement ViewProjectionInterface
     */
    public function __construct(protected readonly string $viewProjectionClass)
    {
        if (!class_exists($viewProjectionClass)) {
            throw new InvalidArgumentException('$viewProjectionClass class provided does not exist.');
        }

        $interfaces = class_implements($viewProjectionClass);

        if (!isset($interfaces[ViewProjectionInterface::class])) {
            throw new InvalidArgumentException("ViewProjection class must be an instance of ViewProjectionInterface.");
        }
    }

    /**
     * Gets the view projection class name configured for this attribute.
     *
     * @return class-string<ViewProjectionInterface> The fully qualified class name of the view projection
     */
    public function getViewProjectionClass(): string
    {
        return $this->viewProjectionClass;
    }
}