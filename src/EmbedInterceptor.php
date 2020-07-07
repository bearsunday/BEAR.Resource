<?php

declare(strict_types=1);

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
        /** @var array<object> $embeds */
        $embeds = $this->reader->getMethodAnnotations($method);
        $this->embedResource($embeds, $ro, $query);

        return $invocation->proceed();
    }

    /**
     * @param array<Embed|object>  $embeds
     * @param array<string, mixed> $query
     *
     * @throws EmbedException
     *
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress MixedMethodCall
     */
    private function embedResource(array $embeds, ResourceObject $ro, array $query) : void
    {
        foreach ($embeds as $embed) {
            if (! $embed instanceof Embed) {
                continue;
            }
            try {
                $templateUri = $this->getFullUri($embed->src, $ro);
                $uri = uri_template($templateUri, $query);
                /** @var Request $request */
                $request = $this->resource->get->uri($uri);
                /** @psalm-suppress MixedArrayAssignment */
                $ro->body[$embed->rel] = clone $request;
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

    /**
     * @return array<string, mixed>
     */
    private function getArgsByInvocation(MethodInvocation $invocation) : array
    {
        $args = $invocation->getArguments()->getArrayCopy();
        $params = $invocation->getMethod()->getParameters();
        $namedParameters = [];
        foreach ($params as $param) {
            /** @psalm-suppress MixedAssignment */
            $namedParameters[(string) $param->name] = array_shift($args);
        }

        return $namedParameters;
    }
}
