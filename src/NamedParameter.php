<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Cache\Cache;

final class NamedParameter implements NamedParameterInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ParameterHandlerInterface
     */
    private $handler;

    public function __construct(Cache $cache, ParameterHandlerInterface $handler)
    {
        $this->cache = $cache;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(array $callable, array $query)
    {
        $id = __CLASS__ . get_class($callable[0]) . $callable[1];
        $names = $this->cache->fetch($id);
        if (! $names) {
            $names = $this->getParameterNames($callable);
            $this->cache->save($id, $names);
        }
        $parameters = $this->handleParams($query, $names);

        return $parameters;
    }

    private function getParameterNames(array $callable)
    {
        $parameters = (new \ReflectionMethod($callable[0], $callable[1]))->getParameters();
        $names = [];
        foreach ($parameters as $parameter) {
            $default = $parameter->isDefaultValueAvailable() === true ? $parameter->getDefaultValue() : new Param(get_class($callable[0]), $callable[1], $parameter->name);
            $names[$parameter->name] = $default;
        }

        return $names;
    }

    /**
     * @param string[] $query
     * @param string[] $names
     *
     * @return array
     */
    private function handleParams(array $query, array $names)
    {
        $parameters = [];
        foreach ($names as $name => $defaultValue) {
            $value = array_key_exists($name, $query) ? $query[$name] : $defaultValue;
            if ($value instanceof Param) {
                $parameter = new \ReflectionParameter([$value->class, $value->method], $value->param);
                $value = $this->handler->handle($parameter, $query);
            }
            $parameters[] = $value;
        }

        return $parameters;
    }
}
