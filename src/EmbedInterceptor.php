<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Exception\LinkException;
use Override;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Set;
use Ray\Di\ProviderInterface;
use ReflectionAttribute;

use function array_map;
use function array_shift;
use function assert;
use function is_array;
use function is_string;
use function sprintf;
use function uri_template;

/** @psalm-import-type Query from Types */
final readonly class EmbedInterceptor implements EmbedInterceptorInterface
{
    private const SELF_LINK = '_self';

    /**
     * @param ProviderInterface<ResourceInterface> $resourceProvider
     *
     * @psalm-pure
     */
    public function __construct(
        #[Set(ResourceInterface::class)]
        private ProviderInterface $resourceProvider,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws EmbedException
     */
    #[Override]
    public function invoke(MethodInvocation $invocation)
    {
        $embeds = array_map(
            static fn (ReflectionAttribute $attribute): Embed => $attribute->newInstance(),
            $invocation->getMethod()->getAttributes(Embed::class),
        );
        $this->embedResource($embeds, $invocation);

        return $invocation->proceed();
    }

    /**
     * Puts the embeds in the body of the invoked resource, overwriting a rel already there.
     *
     * @param list<Embed>              $embeds
     * @param MethodInvocation<object> $invocation the src templates are expanded with its arguments
     *
     * @throws EmbedException
     */
    public function embedResource(array $embeds, MethodInvocation $invocation): void
    {
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $query = $this->getArgsByInvocation($invocation);
        $resource = $this->resourceProvider->get();

        foreach ($embeds as $embed) {
            try {
                $templateUri = $this->getFullUri($embed->src, $ro);
                $uri = uri_template($templateUri, $query);
                $request = $resource->newRequest(Method::GET, $uri);
                assert($request instanceof Request);
                $this->prepareBody($ro, $embed);

                if ($embed->rel === self::SELF_LINK) {
                    $this->linkSelf($request, $ro);

                    continue;
                }

                assert(is_array($ro->body));

                $ro->body[$embed->rel] = clone $request;
            } catch (BadRequestException $e) {
                // wrap ResourceNotFound or Uri exception
                throw new EmbedException($embed->src, 500, $e);
            }
        }
    }

    /** @psalm-capabilities read-props */
    private function getFullUri(string $uri, ResourceObject $ro): string
    {
        if ($uri[0] === '/') {
            $uri = "{$ro->uri->scheme}://{$ro->uri->host}" . $uri;
        }

        return $uri;
    }

    /** @psalm-capabilities read-props|write-props */
    public function prepareBody(ResourceObject $ro, Embed $embed): void
    {
        if ($ro->body === null) {
            $ro->body = [];
        }

        if (! is_array($ro->body)) {
            throw new LinkException($embed->rel); // @codeCoverageIgnore
        }
    }

    /**
     * @param MethodInvocation<object> $invocation
     *
     * @return Query
     */
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

    public function linkSelf(Request $request, ResourceObject $ro): void
    {
        $result = $request();
        if (! is_array($result->body)) {
            throw new EmbedException(sprintf(
                '"_self" embed of %s returned no body.',
                (string) $result->uri,
            ));
        }

        /** @var mixed $value */
        foreach ($result->body as $key => $value) {
            assert(is_string($key));
            /** @psalm-suppress MixedArrayAssignment */
            $ro->body[$key] = $value; // @phpstan-ignore-line
        }

        $ro->code = $result->code;
    }
}
