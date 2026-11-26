<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\LinkException;
use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use Override;
use Ray\Di\Di\Set;
use Ray\Di\ProviderInterface;
use ReflectionMethod;

use function array_map;
use function array_merge;
use function implode;
use function is_array;
use function preg_match;
use function sprintf;
use function strtolower;
use function ucfirst;

/** @internal */
final class Linker implements LinkerInterface
{
    /** A Location a link follows: a path, resolved against the scheme and host of the response */
    private const LOCATION_PATH = '#\A/(?!/)#';

    /**
     * @param ProviderInterface<LinkCrawlerInterface> $linkCrawlerProvider
     *
     * @psalm-pure
     */
    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly FactoryInterface $factory,
        #[Set(LinkCrawlerInterface::class)]
        private readonly ProviderInterface $linkCrawlerProvider,
        private readonly Anchor $anchor,
    ) {
    }

    #[Override]
    public function invoke(AbstractRequest $request)
    {
        $linkCrawler = $this->linkCrawlerProvider->get();

        return $this->invokeRecursive($request, $linkCrawler);
    }

    /**
     * @throws LinkException
     * @throws LinkQueryException
     * @throws LinkRelException
     */
    private function invokeRecursive(AbstractRequest $request, LinkCrawlerInterface $linkCrawler): ResourceObject
    {
        $this->invoker->invoke($request);
        [$current, $method] = $this->located(clone $request->resourceObject, $request->method);
        if ($current->code >= Code::BAD_REQUEST) {
            return $current;
        }

        foreach ($request->links as $link) {
            if (! is_array($current->body)) {
                throw new LinkQueryException('Only array is allowed for link in ' . $current::class, 500);
            }

            $annotations = $this->getLinkAnnotations($current, $method->value);
            if ($link->type === LinkType::CRAWL_LINK) {
                $current = $this->annotationCrawl($annotations, $link, $current, $linkCrawler);

                continue;
            }

            $linkedRequest = $this->annotationRel($annotations, $link, $current, $method);
            [$linked, $linkedMethod] = $this->located($this->invoker->invoke($linkedRequest), $linkedRequest->method);
            if ($link->type === LinkType::SELF_LINK) {
                $current = $linked;
                if ($current->code >= Code::BAD_REQUEST) {
                    return $current;
                }

                // The next link is read from the method the linked resource was requested with
                $method = $linkedMethod;

                continue;
            }

            // new
            $current->body[$link->key] = $linked->body;
        }

        return $current;
    }

    /**
     * The resource that answers a response, and the method it was requested with
     *
     * A 303, or a 201 with a Location, is answered by the resource its Location names, requested once with GET.
     *
     * @return array{ResourceObject, Method}
     *
     * @throws LinkException
     */
    private function located(ResourceObject $response, Method $method): array
    {
        if (! $this->redirects($response)) {
            return [$response, $method];
        }

        $location = $this->location($response);
        if ($location === null || preg_match(self::LOCATION_PATH, $location) !== 1) {
            throw new LinkException(sprintf('%s answered %d with the Location %s; a link follows only a path', (string) $response->uri, $response->code, $location ?? '(none)'), 500);
        }

        $ro = $this->factory->newInstance("{$response->uri->scheme}://{$response->uri->host}{$location}");
        $ro->uri->method = Method::GET->value;
        $located = $this->invoker->invoke(new Request($this->invoker, $ro, Method::GET, $ro->uri->query));
        if ($this->redirects($located)) {
            throw new LinkException(sprintf('%s, the Location of %s, redirects again; a link follows one Location', (string) $located->uri, (string) $response->uri), 500);
        }

        return [$located, Method::GET];
    }

    /** A 303, or a 201 that names the created resource in a Location */
    private function redirects(ResourceObject $ro): bool
    {
        return $ro->code === Code::SEE_OTHER || ($ro->code === Code::CREATED && $this->location($ro) !== null);
    }

    /** The Location header of a response, whatever the case of its name, as HTTP reads it */
    private function location(ResourceObject $ro): string|null
    {
        foreach ($ro->headers as $name => $value) {
            if (strtolower($name) === 'location') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get Link annotations from a ResourceObject method using PHP 8 attributes
     *
     * @return list<Link>
     */
    private function getLinkAnnotations(ResourceObject $ro, string $method): array
    {
        $classMethod = 'on' . ucfirst($method);
        $refMethod = new ReflectionMethod($ro, $classMethod);
        $attributes = $refMethod->getAttributes(Link::class);

        return array_map(
            static fn ($attr) => $attr->newInstance(),
            $attributes,
        );
    }

    /**
     * The request of a self or new link, made as Resource::href() makes it
     *
     * The method the link declares requests its href expanded with the body overlaid by the link query; the link query
     * is overlaid on the expanded query.
     *
     * @param list<Link> $annotations
     *
     * @throws LinkRelException
     */
    private function annotationRel(array $annotations, LinkType $link, ResourceObject $current, Method $from): Request
    {
        foreach ($annotations as $annotation) {
            if ($annotation->rel !== $link->key) {
                continue;
            }

            [$method, $uri] = $this->anchor->methodUri($annotation, $current->body, $link->query);
            $ro = $this->factory->newInstance($uri);
            $ro->uri->method = $method;
            $ro->uri->query = array_merge($ro->uri->query, $link->query);

            return new Request($this->invoker, $ro, Method::from($method), $ro->uri->query);
        }

        $declared = implode(',', array_map(static fn (Link $annotation): string => $annotation->rel, $annotations));

        throw new LinkRelException(sprintf('rel:%s class:%s method:on%s declared:%s', $link->key, $current::class, ucfirst($from->value), $declared), 500);
    }

    /**
     * Link annotation crawl - delegate to LinkCrawlerInterface
     *
     * @param list<Link> $annotations
     */
    private function annotationCrawl(array $annotations, LinkType $link, ResourceObject $current, LinkCrawlerInterface $linkCrawler): ResourceObject
    {
        $isList = $linkCrawler->isList($current->body);
        /** @var list<array<string, mixed>> $bodyList */
        $bodyList = $isList ? (array) $current->body : [$current->body];

        // Delegate to LinkCrawler
        $linkCrawler->crawl($annotations, $link, $bodyList);

        $current->body = $isList ? $bodyList : $bodyList[0];

        return $current;
    }
}
