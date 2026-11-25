<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use Override;
use Ray\Di\Di\Set;
use Ray\Di\ProviderInterface;
use ReflectionMethod;

use function array_map;
use function array_merge;
use function is_array;
use function ucfirst;

/** @internal */
final class Linker implements LinkerInterface
{
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
    ) {
    }

    #[Override]
    public function invoke(AbstractRequest $request)
    {
        $linkCrawler = $this->linkCrawlerProvider->get();

        return $this->invokeRecursive($request, $linkCrawler);
    }

    /**
     * @throws LinkQueryException
     * @throws LinkRelException
     */
    private function invokeRecursive(AbstractRequest $request, LinkCrawlerInterface $linkCrawler): ResourceObject
    {
        $this->invoker->invoke($request);
        $current = clone $request->resourceObject;
        if ($current->code >= Code::BAD_REQUEST) {
            return $current;
        }

        $method = $request->method;
        foreach ($request->links as $link) {
            if (! is_array($current->body)) {
                throw new LinkQueryException('Only array is allowed for link in ' . $current::class, 500);
            }

            $annotations = $this->getLinkAnnotations($current, $method->value);
            if ($link->type === LinkType::CRAWL_LINK) {
                $current = $this->annotationCrawl($annotations, $link, $current, $linkCrawler);

                continue;
            }

            $linkedRequest = $this->annotationRel($annotations, $link, $current);
            $linked = $this->invoker->invoke($linkedRequest);
            if ($link->type === LinkType::SELF_LINK) {
                $current = $linked;
                if ($current->code >= Code::BAD_REQUEST) {
                    return $current;
                }

                // The next link is read from the method the linked resource was requested with
                $method = $linkedRequest->method;

                continue;
            }

            // new
            $current->body[$link->key] = $linked->body;
        }

        return $current;
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
    private function annotationRel(array $annotations, LinkType $link, ResourceObject $current): Request
    {
        foreach ($annotations as $annotation) {
            if ($annotation->rel !== $link->key) {
                continue;
            }

            [$method, $uri] = Anchor::methodUri($annotation, $current->body, $link->query);
            $ro = $this->factory->newInstance($uri);
            $ro->uri->method = $method;
            $ro->uri->query = array_merge($ro->uri->query, $link->query);

            return new Request($this->invoker, $ro, Method::from($method), $ro->uri->query);
        }

        throw new LinkRelException("rel:{$link->key} class:" . $current::class, 500);
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
