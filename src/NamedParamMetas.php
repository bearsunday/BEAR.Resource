<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\RequestParamInterface;
use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use LogicException;
use Ray\Di\Di\Assisted;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function get_class;
use function is_array;
use function is_object;

use const PHP_VERSION_ID;

final class NamedParamMetas implements NamedParamMetasInterface
{
    /** @var Cache */
    private $cache;

    /** @var Reader */
    private $reader;

    public function __construct(Cache $cache, Reader $reader)
    {
        $this->cache = $cache;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(callable $callable): array
    {
        if (! is_array($callable) || ! is_object($callable[0])) {
            throw new LogicException('callable should be an array'); // @codeCoverageIgnore
        }

        $cacheId = self::class . get_class($callable[0]) . $callable[1];
        /** @var array<string, ParamInterface>|false $names */
        $names = $this->cache->fetch($cacheId);
        if ($names) {
            return $names;
        }

        $method = new ReflectionMethod($callable[0], $callable[1]);
        $namedParamMetas = false;
        if (PHP_VERSION_ID >= 80000) {
            $namedParamMetas = $this->getNamedParamMetasByAttribute($method);
        }

        if (! $namedParamMetas) {
            $namedParamMetas = $this->getNamedParamMetasByAnnotation($method);
        }

        $this->cache->save($cacheId, $namedParamMetas);

        return $namedParamMetas;
    }

    /**
     * @return array<string, AssistedWebContextParam|ParamInterface>
     */
    private function getNamedParamMetasByAnnotation(ReflectionMethod $method)
    {
        $parameters = $method->getParameters();
        /** @var array<object> $annotations */
        $annotations = $this->reader->getMethodAnnotations($method);
        $assistedNames = $this->getAssistedNames($annotations);
        $webContext = $this->getWebContext($annotations);

        return $this->addNamedParams($parameters, $assistedNames, $webContext);
    }

    /**
     * @return array<string, ParamInterface>
     */
    private function getNamedParamMetasByAttribute(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();
        $names = [];
        foreach ($parameters as $parameter) {
            /** @var array<ReflectionAttribute> $refAttribute */
            $refAttribute = $parameter->getAttributes(RequestParamInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($refAttribute) {
                /** @var RequestParamInterface $resourceParam */
                $resourceParam = $refAttribute[0]->newInstance();
                if ($resourceParam instanceof ResourceParam) {
                    $names[$parameter->name] = new AssistedResourceParam($resourceParam);
                }
            }
        }

        return $names;
    }

    /**
     * @param array<Assisted|object|ResourceParam> $annotations
     *
     * @return array<string, ParamInterface>
     */
    private function getAssistedNames(array $annotations): array
    {
        $names = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = new AssistedResourceParam($annotation);
            }

            if (! ($annotation instanceof Assisted)) {
                continue;
            }

            $names = $this->setAssistedAnnotation($names, $annotation);
        }

        return $names;
    }

    /**
     * @param array<object> $annotations
     *
     * @return array<string, AbstractWebContextParam>
     */
    private function getWebContext(array $annotations): array
    {
        $webcontext = [];
        foreach ($annotations as $annotation) {
            if (! ($annotation instanceof AbstractWebContextParam)) {
                continue;
            }

            $webcontext[$annotation->param] = $annotation;
        }

        return $webcontext;
    }

    /**
     * @param array<string, ParamInterface> $names
     *
     * @return array<string, ParamInterface>
     */
    private function setAssistedAnnotation(array $names, Assisted $assisted): array
    {
        foreach ($assisted->values as $assistedParam) {
            $names[$assistedParam] = new AssistedParam();
        }

        return $names;
    }

    /**
     * @param ReflectionParameter[]                  $parameters
     * @param array<string, ParamInterface>          $assistedNames
     * @param array<string, AbstractWebContextParam> $webcontext
     *
     * @return (AssistedWebContextParam|ParamInterface)[]
     * @psalm-return array<string, AssistedWebContextParam|ParamInterface>
     */
    private function addNamedParams(array $parameters, array $assistedNames, array $webcontext): array
    {
        $names = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
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

    /**
     * @return DefaultParam|NoDefaultParam
     * @psalm-return DefaultParam<mixed>|NoDefaultParam
     */
    private function getDefault(ReflectionParameter $parameter)
    {
        return $parameter->isDefaultValueAvailable() === true ? new DefaultParam($parameter->getDefaultValue()) : new NoDefaultParam();
    }

    /**
     * @return ClassParam|OptionalParam|RequiredParam
     * @psalm-return ClassParam|OptionalParam<mixed>|RequiredParam
     */
    private function getParam(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return new ClassParam($type, $parameter);
        }

        return $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam();
    }
}
