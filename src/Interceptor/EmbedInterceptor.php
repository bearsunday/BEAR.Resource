<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use BEAR\Resource\NamedArgsInterface;
use BEAR\Resource\Exception\Embed as EmbedException;
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
     * @var \BEAR\Resource\NamedArgsInterface
     */
    private $namedArgs;

    /**
     * @param ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource, Reader $reader, NamedArgsInterface $namedArgs)
    {
        $this->resource = $resource;
        $this->reader = $reader;
        $this->namedArgs = $namedArgs;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $resourceObject = $invocation->getThis();
        $method = $invocation->getMethod();
        $query = $this->namedArgs->get($invocation);

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
            if (! $embed instanceof Embed || ! ($resourceObject->body[$embed->rel] instanceof ResourceInterface) ) {
                continue;
            }

            $resourceObject->body[$embed->rel] = $resourceObject->body[$embed->rel]->request();
        }

        return $result;
    }
}
