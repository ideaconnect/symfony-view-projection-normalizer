<?php

namespace IDCT\Mvc\Model;

/**
 * Normalizable Interface
 *
 * Marker interface for entities or models that can be processed by the DefaultViewProjectionNormalizer.
 * Classes implementing this interface are candidates for automatic view projection conversion
 * when they also have the DefaultViewProjection attribute configured.
 *
 * This interface serves as a safety mechanism to ensure only intended classes
 * are processed by the view projection normalization system.
 *
 * @package IDCT\Mvc\Model
 */
interface NormalizableInterface
{

}