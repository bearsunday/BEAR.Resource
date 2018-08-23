<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\EmbedException;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

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
     * @param Reader            $reader
     */
    public function __construct(ResourceInterface $resource, Reader $reader)
    {
        $this->resource = clone $resource;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BEAR\Resource\Exception\EmbedException
     */
    public function invoke(MethodInvocation $invocation)
    {
        /** @var ResourceObject $ro */
        $ro = $invocation->getThis();
        $method = $invocation->getMethod();
        $query = $this->getArgsByInvocation($invocation);
        $embeds = $this->reader->getMethodAnnotations($method);
        $this->embedResource($embeds, $ro, $query);
        $result = $invocation->proceed();

        return $result;
    }

    /**
     * @param Embed[]        $embeds
     * @param ResourceObject $ro
     * @param array          $query
     *
     * @throws EmbedException
     */
    private function embedResource(array $embeds, ResourceObject $ro, array $query)
    {
        foreach ($embeds as $embed) {
            /* @var $embed Embed */
            if (! $embed instanceof Embed) {
                continue;
            }
            try {
                $templateUri = $this->getFullUri($embed->src, $ro);
                $uri = uri_template($templateUri, $query);
                $ro->body[$embed->rel] = clone $this->resource->uri($uri);
            } catch (BadRequestException $e) {
                // wrap ResourceNotFound or Uri exception
                throw new EmbedException($embed->src, 500, $e);
            }
        }
    }

    private function getFullUri(string $uri, ResourceObject $ro) : string
    {
        if ($uri[0] === '/') {
            $uri = "{$ro->uri->scheme}://{$ro->uri->host}" . $uri;
        }

        return $uri;
    }

    private function getArgsByInvocation(MethodInvocation $invocation) : array
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
