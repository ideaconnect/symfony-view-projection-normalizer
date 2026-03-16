<?php

namespace IDCT\Mvc\Model;

/**
 * View Projection Interface
 *
 * Marker interface for view projection classes that can be used with the DefaultViewProjection attribute.
 * View projections implementing this interface serve as an intermediate layer between your data models
 * and the final serialized output, allowing you to control exactly what data is exposed
 * and how it's structured.
 *
 * @package IDCT\Mvc\Model
 */
interface ViewProjectionInterface
{
	public function __construct(NormalizableInterface $source);
}