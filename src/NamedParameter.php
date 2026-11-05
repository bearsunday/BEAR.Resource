<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use InvalidArgumentException;
use Override;
use Ray\Di\InjectorInterface;

final readonly class NamedParameter implements NamedParameterInterface
{
    /** @psalm-pure */
    public function __construct(
        private NamedParamMetasInterface $paramMetas,
        private InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ParameterException Thrown when the query parameter is invalid.
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function getParameters(callable $callable, array $query): array
    {
        $metas = ($this->paramMetas)($callable);
        $parameters = [];
        foreach ($metas as $varName => $param) {

            /** @psalm-suppress MixedAssignment */
            try {
                $parameters[$varName] = $param($varName, $query, $this->injector);
            } catch (InvalidArgumentException $e) {
                $code = $e->getCode() !== 0 ? $e->getCode() : Code::BAD_REQUEST;

                throw new ParameterException($varName, $code, $e);
            }
        }

        return $parameters;
    }
}
