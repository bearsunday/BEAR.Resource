<?php

declare(strict_types=1);

namespace BEAR\Resource\Input;

use BEAR\Resource\Annotation\Input;
use Generator;
use ReflectionMethod;
use ReflectionParameter;

use function count;

/**
 * Iterator for parameters with Input attribute
 */
final class InputAttributeIterator
{
    /**
     * Yield parameters that have Input attribute
     *
     * @param ReflectionMethod $method
     *
     * @return Generator<string, ReflectionParameter>
     */
    public function __invoke(ReflectionMethod $method): Generator
    {
        foreach ($method->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(Input::class);
            if (count($attributes) <= 0) {
                continue;
            }

            yield $parameter->getName() => $parameter;
        }
    }

    /**
     * Get Input attribute instance from parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return Input|null
     */
    public function getInputAttribute(ReflectionParameter $parameter): Input|null
    {
        $attributes = $parameter->getAttributes(Input::class);
        if (count($attributes) === 0) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Check if method has any Input attribute parameters
     *
     * @param ReflectionMethod $method
     *
     * @return bool
     */
    public function hasInputParameters(ReflectionMethod $method): bool
    {
        foreach ($method->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(Input::class);
            if (count($attributes) > 0) {
                return true;
            }
        }

        return false;
    }
}
