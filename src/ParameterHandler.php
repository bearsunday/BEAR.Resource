<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\InjectorInterface;

/**
 * Resource parameter handler
 */
class ParameterHandler implements ParameterHandlerInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(Reader $reader, InjectorInterface $injector)
    {
        $this->reader = $reader;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BEAR\Resource\Exception\ParameterException
     */
    public function handle(\ReflectionParameter $parameter, array $query)
    {
        $func = $parameter->getDeclaringFunction();
        $method = new \ReflectionMethod($parameter->getDeclaringClass()->name, $func->name);
        $parameter->getDeclaringFunction()->name;
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam && $annotation->param === $parameter->name) {
                return $this->getResourceParam($annotation, $query);
            }
        }
        (new VoidParameterHandler)->handle($parameter, $query);
    }

    /**
     * @param ResourceParam $resourceParam
     * @param array         $query
     *
     * @return mixed
     */
    private function getResourceParam(ResourceParam $resourceParam, array $query)
    {
        $uri = $resourceParam->templated === true ? uri_template($resourceParam->uri, $query) : $resourceParam->uri;
        $resource = $this->injector->getInstance(ResourceInterface::class);
        $resourceResult = $resource->get->uri($uri)->eager->request();
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);

        return $resourceResult[$fragment];
    }
}
