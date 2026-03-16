<?php

namespace IDCT\Mvc\Model;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Cacheable View Projection Interface
 *
 * Extended interface for view projections that support caching mechanisms.
 * View projections implementing this interface can provide a cache key
 * that can be used by caching layers to store and retrieve normalized data.
 *
 * This interface extends both ViewProjectionInterface (implicitly through usage)
 * and NormalizerInterface to provide full normalization capabilities
 * with caching support.
 *
 * @package IDCT\Mvc\Model
 */
interface CacheableViewProjectionInterface extends NormalizerInterface
{
    /**
    * Gets the cache key for this view projection instance.
     *
     * The cache key should be unique and deterministic based on the
     * underlying data to ensure proper cache invalidation and retrieval.
     *
    * @return string A unique cache key for this view projection instance
     */
    public function getCacheKey(): string;
}