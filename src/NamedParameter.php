<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;

final class NamedParameter implements NamedParameterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParameters(array $callable, array $query)
    {
        $namedArgs = $query;
        $method = new \ReflectionMethod($callable[0], $callable[1]);
        $refParameters = $method->getParameters();
        $parameters = [];
        foreach ($refParameters as $parameter) {
            if (isset($namedArgs[$parameter->name])) {
                $parameters[] = $namedArgs[$parameter->name];
                continue;
            }
            $parameters[] = $this->getParameter($callable, $parameter);
        }

        return $parameters;
    }

    /**
     * @param array                $callable
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     */
    private function getParameter(array $callable, \ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable() === true) {
            return $parameter->getDefaultValue();
        }
        $msg = '$' . "{$parameter->name} in " . get_class($callable[0]) . '::' . $callable[1] . '()';
        throw new ParameterException($msg, 400);
    }
}
