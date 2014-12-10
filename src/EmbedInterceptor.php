<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\EmbedException;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;

final class EmbedInterceptor implements MethodInterceptor
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource, Reader $reader)
    {
        $this->resource = $resource;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $resourceObject = $invocation->getThis();
        $method = $invocation->getMethod();
        $query = $this->getArgsByInvocation($invocation);
        $embeds = $this->reader->getMethodAnnotations($method);
        $this->embedResource($embeds, $resourceObject, $query);
        $result =  $invocation->proceed();
        $this->evaluateEmbedResources($embeds, $resourceObject);

        return $result;
    }

    /**
     * @param Embed[]        $embeds
     * @param ResourceObject $resourceObject
     * @param array          $query
     */
    private function embedResource(array $embeds, ResourceObject $resourceObject, array $query)
    {
        foreach ($embeds as $embed) {
            /** @var $embed Embed */
            if (! $embed instanceof Embed) {
                continue;
            }

            try {
                $uri = uri_template($embed->src, $query);
                $resourceObject->body[$embed->rel] = clone $this->resource->get->uri($uri);
            } catch (BadRequestException $e) {
                // wrap ResourceNotFound or Uri exception
                throw new EmbedException($embed->src, 500, $e);
            }
        }
    }

    /**
     * @param Embed[]        $embeds
     * @param ResourceObject $resourceObject
     */
    private function evaluateEmbedResources(array $embeds, ResourceObject $resourceObject)
    {
        foreach ($embeds as $embed) {
            /** @var $embed Embed */
            if (! $embed instanceof Embed || ! ($resourceObject->body[$embed->rel] instanceof RequestInterface)) {
                continue;
            }
            $resourceObject->body[$embed->rel] = $resourceObject->body[$embed->rel]->request();
        }
    }

    /**
     * @param MethodInvocation $invocation
     *
     * @return array
     */
    private function getArgsByInvocation(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments()->getArrayCopy();
        $params = $invocation->getMethod()->getParameters();
        $namedParameters = [];
        foreach ($params as $param) {
            $namedParameters[$param->name] = array_shift($args);
        }

        return $namedParameters;
    }
}
