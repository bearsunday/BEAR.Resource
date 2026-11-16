<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\AbstractEmbedPage;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Exception\LinkException;
use Override;
use Ray\Aop\MethodInvocation;
use Ray\Aop\WeavedInterface;
use Ray\Di\Di\Set;
use Ray\Di\ProviderInterface;
use ReflectionAttribute;
use ReflectionClass;

use function array_key_exists;
use function assert;
use function is_array;
use function uri_template;

/** A rel already in the body, such as one from a method #[Embed] (which runs first), is kept. */
final readonly class EmbedPageInterceptor implements EmbedPageInterceptorInterface
{
    /** @param ProviderInterface<ResourceInterface> $resourceProvider */
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
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $ro->body ??= [];
        $class = new ReflectionClass($ro);
        if ($ro instanceof WeavedInterface) {
            $class = $class->getParentClass();
            assert($class instanceof ReflectionClass);
        }

        /** @var array<string, mixed> $query */
        $query = $invocation->getNamedArguments()->getArrayCopy();
        foreach ($class->getAttributes(AbstractEmbedPage::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            foreach ($attribute->newInstance()->embed() as $rel => $src) {
                $this->embed($ro, $rel, $src, $query);
            }
        }

        return $invocation->proceed();
    }

    /**
     * @param array<string, mixed> $query
     *
     * @throws EmbedException
     */
    private function embed(ResourceObject $ro, string $rel, string $src, array $query): void
    {
        if (! is_array($ro->body)) {
            throw new LinkException($rel); // @codeCoverageIgnore
        }

        if (array_key_exists($rel, $ro->body)) {
            return;
        }

        $uri = $src[0] === '/' ? "{$ro->uri->scheme}://{$ro->uri->host}{$src}" : $src;
        try {
            $ro->body[$rel] = clone $this->resourceProvider->get()->newRequest(Method::GET, uri_template($uri, $query));
        } catch (BadRequestException $e) {
            throw new EmbedException($src, 500, $e);
        }
    }
}
