<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use function is_object;
use Ray\Di\Di\Assisted;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;

final class NamedParamMetas implements NamedParamMetasInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Cache $cache, Reader $reader)
    {
        $this->cache = $cache;
        $this->reader = $reader;
    }

    /**
     * @return array|ParamInterface[]
     */
    public function __invoke(callable $callable) : array
    {
        if (! is_array($callable) || ! is_object($callable[0])) {
            throw new \LogicException('callable should be an array'); // @codeCoverageIgnore
        }
        $cacheId = __CLASS__ . get_class($callable[0]) . $callable[1];
        $names = $this->cache->fetch($cacheId);
        if ($names) {
            return $names;
        }
        $method = new \ReflectionMethod($callable[0], $callable[1]);
        $parameters = $method->getParameters();
        $annotations = $this->reader->getMethodAnnotations($method);
        $assistedNames = $this->getAssistedNames($annotations);
        $webContext = $this->getWebContext($annotations);
        $namedParamMetas = $this->addNamedParams($parameters, $assistedNames, $webContext);
        $this->cache->save($cacheId, $namedParamMetas);

        return $namedParamMetas;
    }

    /**
     * @param array<Assisted|ResourceParam> $annotations
     *
     * @return array<string, ParamInterface>
     */
    private function getAssistedNames(array $annotations) : array
    {
        $names = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = new AssistedResourceParam($annotation);
            }
            if ($annotation instanceof Assisted) {
                $names = $this->setAssistedAnnotation($names, $annotation);
            }
        }

        return $names;
    }

    /**
     * @param array<object> $annotations
     *
     * @return array<string, AbstractWebContextParam>
     */
    private function getWebContext(array $annotations) : array
    {
        $webcontext = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AbstractWebContextParam) {
                $webcontext[$annotation->param] = $annotation;
            }
        }

        return $webcontext;
    }

    /**
     * @param array<string, ParamInterface> $names
     *
     * @return array<string, ParamInterface>
     */
    private function setAssistedAnnotation(array $names, Assisted $assisted) : array
    {
        foreach ($assisted->values as $assistedParam) {
            $names[(string) $assistedParam] = new AssistedParam;
        }

        return $names;
    }

    /**
     * @param \ReflectionParameter[]                 $parameters
     * @param array<string, ParamInterface>          $assistedNames
     * @param array<string, AbstractWebContextParam> $webcontext
     *
     * @return ParamInterface[]
     */
    private function addNamedParams(array $parameters, array $assistedNames, array $webcontext) : array
    {
        $names = [];
        foreach ($parameters as $parameter) {
            $name = (string) $parameter->name;
            if (isset($assistedNames[$name])) {
                $names[$name] = $assistedNames[$parameter->name];

                continue;
            }
            if (isset($webcontext[$name])) {
                $default = $this->getDefault($parameter);
                $names[$name] = new AssistedWebContextParam($webcontext[$name], $default);

                continue;
            }
            $names[$name] = $this->getParam($parameter);
        }

        return $names;
    }

    private function getDefault(\ReflectionParameter $parameter) : ParamInterface
    {
        return $parameter->isDefaultValueAvailable() === true ? new DefaultParam($parameter->getDefaultValue()) : new NoDefaultParam();
    }

    private function getParam(\ReflectionParameter $parameter) : ParamInterface
    {
        $class = $parameter->getClass();
        if ($class instanceof \ReflectionClass) {
            return new ClassParam($class, $parameter);
        }

        return $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam;
    }
}
