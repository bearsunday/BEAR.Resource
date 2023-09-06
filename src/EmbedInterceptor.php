<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Exception\LinkException;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function array_shift;
use function assert;
use function is_array;
use function uri_template;

final class EmbedInterceptor implements MethodInterceptor
{
    private ResourceInterface $resource;

    public function __construct(
        ResourceInterface $resource,
    ) {
        $this->resource = clone $resource;
    }

    /**
     * {@inheritDoc}
     *
     * @throws EmbedException
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $query = $this->getArgsByInvocation($invocation);
        $embeds = $invocation->getMethod()->getAnnotations();
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
    private function embedResource(array $embeds, ResourceObject $ro, array $query): void
    {
        foreach ($embeds as $embed) {
            if (! $embed instanceof Embed) {
                continue;
            }

            try {
                $templateUri = $this->getFullUri($embed->src, $ro);
                $uri = uri_template($templateUri, $query);
                /** @var Request $request */ // phpcs:ignore SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
                $request = $this->resource->get->uri($uri);
                if ($ro->body === null) {
                    $ro->body = [];
                }

                if (! is_array($ro->body)) {
                    throw new LinkException($embed->rel); // @codeCoverageIgnore
                }

                $ro->body[$embed->rel] = clone $request;
            } catch (BadRequestException $e) {
                // wrap ResourceNotFound or Uri exception
                throw new EmbedException($embed->src, 500, $e);
            }
        }
    }

    private function getFullUri(string $uri, ResourceObject $ro): string
    {
        if ($uri[0] === '/') {
            $uri = "{$ro->uri->scheme}://{$ro->uri->host}" . $uri;
        }

        return $uri;
    }

    /** @return array<string, mixed> */
    private function getArgsByInvocation(MethodInvocation $invocation): array
    {
        /** @var list<scalar> $args */
        $args = $invocation->getArguments()->getArrayCopy();
        $params = $invocation->getMethod()->getParameters();
        $namedParameters = [];
        foreach ($params as $param) {
            $namedParameters[$param->name] = array_shift($args);
        }

        return $namedParameters;
    }
}
