<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Override;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use ReflectionAttribute;
use Rize\UriTemplate\Node\Expression;
use Rize\UriTemplate\Node\Variable;
use Rize\UriTemplate\Parser;

use function assert;
use function implode;
use function in_array;
use function is_array;
use function preg_match;
use function sprintf;
use function str_starts_with;

/**
 * Appends the GET #[Link] links of a 200 response whose rel is in RELS to the RFC 8288 Link header
 *
 * @internal
 * @psalm-import-type Body from Types
 */
final readonly class LinkHeaderInterceptor implements MethodInterceptor
{
    public const RELS = ['prefetch', 'canonical', 'next', 'prev', 'first', 'last'];

    /** A client fetches every prefetch link, so a response declares this many at most */
    private const PREFETCH_LIMIT = 5;

    public function __construct(
        private HalLinker $linker,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * An onGet() that returns its body instead of the ResourceObject is expanded with the returned value.
     */
    #[Override]
    public function invoke(MethodInvocation $invocation): mixed
    {
        /** @psalm-suppress MixedAssignment */
        $result = $invocation->proceed();
        $ro = $result instanceof ResourceObject ? $result : $invocation->getThis();
        assert($ro instanceof ResourceObject);
        if ($ro->code !== 200) {
            return $result;
        }

        /** @psalm-suppress MixedAssignment */
        $body = $result instanceof ResourceObject ? $ro->body : $result;
        $links = $this->links($invocation->getMethod()->getAttributes(Link::class), is_array($body) ? $body : []);
        if ($links !== '') {
            $ro->headers['Link'] = isset($ro->headers['Link']) ? $ro->headers['Link'] . ', ' . $links : $links;
        }

        return $result;
    }

    /**
     * @param list<ReflectionAttribute<Link>> $attributes
     * @param Body                            $body
     */
    private function links(array $attributes, array $body): string
    {
        $links = [];
        $prefetchLeft = self::PREFETCH_LIMIT;
        foreach ($attributes as $attribute) {
            $link = $attribute->newInstance();
            $href = $this->href($link, $body);
            if ($href === null) {
                continue;
            }

            if ($link->rel === 'prefetch') {
                if ($prefetchLeft === 0) {
                    continue;
                }

                $prefetchLeft--;
            }

            $links[] = sprintf('<%s>; rel="%s"', $href, $link->rel);
        }

        return implode(', ', $links);
    }

    /**
     * The href a client can fetch, or null for a link that is not output
     *
     * @param Body $body
     */
    private function href(Link $link, array $body): string|null
    {
        if (! in_array($link->rel, self::RELS, true) || $link->method !== Link::DEFAULT_METHOD || ! $this->isDefined($link->href, $body)) {
            return null;
        }

        $href = $this->linker->expandHref($link, $body);
        $isPath = str_starts_with($href, '/') && ! str_starts_with($href, '//');

        return $isPath || preg_match('#^https?://#i', $href) === 1 ? $href : null;
    }

    /**
     * Whether every template variable expands to a value: not missing, null, '', [] or false
     *
     * @param Body $body
     */
    private function isDefined(string $href, array $body): bool
    {
        foreach ((new Parser())->parse($href) as $node) {
            if (! $node instanceof Expression) {
                continue;
            }

            /** @var list<Variable> $variables */
            $variables = $node->getVariables();
            foreach ($variables as $variable) {
                if (in_array($body[$variable->name] ?? null, [null, '', [], false], true)) {
                    return false;
                }
            }
        }

        return true;
    }
}
