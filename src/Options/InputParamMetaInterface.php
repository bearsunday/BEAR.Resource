<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use ReflectionMethod;

/**
 * Input attribute parameter metadata extractor
 *
 * Extracts metadata from Input attribute parameters and flattens
 * them into query parameter specifications for OPTIONS responses
 */
interface InputParamMetaInterface
{
    /**
     * Generate flattened query parameter metadata from Input attribute parameters
     *
     * Returns empty array if no Input attribute parameters are found
     *
     * @param ReflectionMethod $method Resource method
     *
     * @return array{parameters?: array<string, array{type?: string, description?: string, default?: string, group?: string, group_description?: string}>, required?: array<int, string>}
     */
    public function get(ReflectionMethod $method): array;
}
