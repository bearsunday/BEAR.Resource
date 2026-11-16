<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Override;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Rize\UriTemplate\Node\Expression;
use Rize\UriTemplate\Node\Variable;
use Rize\UriTemplate\Parser;

use function assert;
use function count;
use function implode;
use function is_array;
use function preg_match;
use function sprintf;
use function str_starts_with;

/**
 * Appends the GET #[Link(rel: 'prefetch')] links of a 200 response to the RFC 8288 Link header
 *
 * @psalm-import-type Body from Types
 */
final readonly class PrefetchLinkHeaderInterceptor implements MethodInterceptor
{
    private const LIMIT = 5;

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
        $body = is_array($body) ? $body : [];
        $links = [];
        foreach ($invocation->getMethod()->getAttributes(Link::class) as $attribute) {
            $link = $attribute->newInstance();
            if ($link->rel !== 'prefetch' || $link->method !== Link::DEFAULT_METHOD || ! $this->isDefined($link->href, $body)) {
                continue;
            }

            $href = $this->linker->expandHref($link, $body);
            if (! str_starts_with($href, '/') && ! preg_match('#^https?://#', $href)) {
                continue;
            }

            $links[] = sprintf('<%s>; rel="prefetch"', $href);
            if (count($links) === self::LIMIT) {
                break;
            }
        }

        if ($links !== []) {
            $link = implode(', ', $links);
            $ro->headers['Link'] = isset($ro->headers['Link']) ? $ro->headers['Link'] . ', ' . $link : $link;
        }

        return $result;
    }

    /**
     * Whether every template variable is set and not null in the body
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
                if (! isset($body[$variable->name])) {
                    return false;
                }
            }
        }

        return true;
    }
}
