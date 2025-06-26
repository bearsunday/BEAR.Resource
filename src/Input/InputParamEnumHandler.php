<?php

declare(strict_types=1);

namespace BEAR\Resource\Input;

use BackedEnum;
use BEAR\Resource\Exception\NotBackedEnumException;
use BEAR\Resource\Exception\ParameterEnumTypeException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use ReflectionEnum;
use ReflectionNamedType;
use UnitEnum;

use function assert;
use function enum_exists;
use function is_a;
use function is_int;
use function is_string;
use function ltrim;
use function preg_replace;
use function strtolower;

final class InputParamEnumHandler
{
    /** @param array<string, mixed> $query */
    public function createEnum(
        string $type,
        string $varName,
        array $query,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        $props = $this->getPropsForClassParam($varName, $query, $isDefaultAvailable, $defaultValue);

        /** @var class-string<UnitEnum> $type */
        $refEnum = new ReflectionEnum($type);
        assert(enum_exists($type));

        if (! $refEnum->isBacked()) {
            throw new NotBackedEnumException($type);
        }

        assert(is_a($type, BackedEnum::class, true));
        if (! (is_int($props) || is_string($props))) {
            if ($isDefaultAvailable) {
                return $defaultValue;
            }

            throw new ParameterEnumTypeException($varName);
        }

        // Get the backing type of the enum
        $backingType = $refEnum->getBackingType();
        if ($backingType instanceof ReflectionNamedType && $backingType->getName() === 'int' && is_string($props)) {
            $props = (int) $props;
        }

        /** @psalm-suppress MixedAssignment */
        $value = $type::tryFrom($props);
        if ($value === null) {
            throw new ParameterInvalidEnumException($varName);
        }

        return $value;
    }

    /** @param array<string, mixed> $query */
    private function getPropsForClassParam(
        string $varName,
        array $query,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        if ($isDefaultAvailable) {
            return $defaultValue;
        }

        throw new ParameterException($varName);
    }
}
