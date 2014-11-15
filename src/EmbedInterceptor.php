<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\Embed as EmbedException;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;

final class EmbedInterceptor implements MethodInterceptor
{
    const LINK_ANNOTATION = 'BEAR\Resource\Annotation\Link';

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
        foreach ($embeds as $embed) {
            /** @var $embed Embed */
            if (! $embed instanceof Embed) {
                continue;
            }

            try {
                $uri = \GuzzleHttp\uri_template($embed->src, $query);
                $resourceObject->body[$embed->rel] = clone $this->resource->get->uri($uri);
            } catch (BadRequest $e) {
                // wrap ResourceNotFound or Uri exception
                throw new EmbedException($embed->src, 500, $e);
            }
        }

        $result =  $invocation->proceed();

        foreach ($embeds as $embed) {
            /** @var $embed Embed */
            if (! $embed instanceof Embed || ! ($resourceObject->body[$embed->rel] instanceof ResourceInterface)) {
                continue;
            }

            $resourceObject->body[$embed->rel] = $resourceObject->body[$embed->rel]->request();
        }

        return $result;
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
            if (isset($namedParameters[$param->name])) {
                throw new \InvalidArgumentException($param->name);
            }
            $namedParameters[$param->name] = array_shift($args);
        }

        return $namedParameters;
    }
}
