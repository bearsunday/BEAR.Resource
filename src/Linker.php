<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use Doctrine\Common\Annotations\Reader;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class Linker implements LinkerInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * memory cache for linker
     *
     * @var array
     */
    private $cache = [];

    public function __construct(
        Reader $reader,
        InvokerInterface $invoker,
        FactoryInterface $factory
    ) {
        $this->reader = $reader;
        $this->invoker = $invoker;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LinkQueryException
     * @throws \BEAR\Resource\Exception\LinkRelException
     */
    public function invoke(AbstractRequest $request)
    {
        $this->invoker->invoke($request);
        $current = clone $request->ro;
        foreach ($request->links as $link) {
            /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $nextResource = $this->annotationLink($link, $current, $request);
            $current = $this->nextLink($link, $current, $nextResource);
        }

        return $current;
    }

    /**
     * How next linked resource treated (add ? replace ?)
     *
     * @param LinkType       $link
     * @param ResourceObject $ro
     * @param mixed          $nextResource
     */
    private function nextLink(LinkType $link, ResourceObject $ro, $nextResource) : ResourceObject
    {
        $nextBody = $nextResource instanceof ResourceObject ? $nextResource->body : $nextResource;

        if ($link->type === LinkType::SELF_LINK) {
            $ro->body = $nextBody;

            return $ro;
        }

        if ($link->type === LinkType::NEW_LINK) {
            $ro->body[$link->key] = $nextBody;

            return $ro;
        }

        // crawl
        return $ro;
    }

    /**
     * Annotation link
     *
     * @throws \BEAR\Resource\Exception\MethodException
     * @throws \BEAR\Resource\Exception\LinkRelException
     * @throws Exception\LinkQueryException
     *
     * @return ResourceObject|mixed
     */
    private function annotationLink(LinkType $link, ResourceObject $current, AbstractRequest $request)
    {
        if (! is_array($current->body)) {
            throw new Exception\LinkQueryException('Only array is allowed for link in ' . get_class($current), 500);
        }
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($current, $classMethod));
        if ($link->type === LinkType::CRAWL_LINK) {
            return $this->annotationCrawl($annotations, $link, $current);
        }

        /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->annotationRel($annotations, $link, $current)->body;
    }

    /**
     * Annotation link (new, self)
     *
     * @param \BEAR\Resource\Annotation\Link[] $annotations
     * @param LinkType                         $link
     * @param ResourceObject                   $current
     *
     * @throws \BEAR\Resource\Exception\UriException
     * @throws \BEAR\Resource\Exception\MethodException
     * @throws Exception\LinkQueryException
     * @throws Exception\LinkRelException
     */
    private function annotationRel(array $annotations, LinkType $link, ResourceObject $current) : ResourceObject
    {
        /* @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($annotations as $annotation) {
            if ($annotation->rel !== $link->key) {
                continue;
            }
            $uri = uri_template($annotation->href, $current->body);
            $rel = $this->factory->newInstance($uri);
            /* @noinspection UnnecessaryParenthesesInspection */
            $request = new Request($this->invoker, $rel, Request::GET, (new Uri($uri))->query);
            $linkedResource = $this->invoker->invoke($request);

            return $linkedResource;
        }
        throw new LinkRelException("rel:{$link->key} class:" . get_class($current), 500);
    }

    /**
     * Link annotation crawl
     *
     * @throws \BEAR\Resource\Exception\MethodException
     */
    private function annotationCrawl(array $annotations, LinkType $link, ResourceObject $current) : ResourceObject
    {
        $isList = $this->isList($current->body);
        $bodyList = $isList ? (array) $current->body : [$current->body];
        foreach ($bodyList as &$body) {
            /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->crawl($annotations, $link, $body);
        }
        unset($body);
        $current->body = $isList ? $bodyList : $bodyList[0];

        return $current;
    }

    /**
     * @throws \BEAR\Resource\Exception\LinkQueryException
     * @throws \BEAR\Resource\Exception\MethodException
     * @throws \BEAR\Resource\Exception\LinkRelException
     * @throws \BEAR\Resource\Exception\UriException
     */
    private function crawl(array $annotations, LinkType $link, array &$body)
    {
        foreach ($annotations as $annotation) {
            /* @var $annotation Link */
            if ($annotation->crawl !== $link->key) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $rel = $this->factory->newInstance($uri);
            /* @noinspection UnnecessaryParenthesesInspection */
            $request = new Request($this->invoker, $rel, Request::GET, (new Uri($uri))->query, [$link], $this);
            $hash = $request->hash();
            if (array_key_exists($hash, $this->cache)) {
                $body[$annotation->rel] = $this->cache[$hash];

                continue;
            }
            $this->cache[$hash] = $body[$annotation->rel] = $this->invoke($request)->body;
        }
    }

    /**
     * @param mixed $value
     */
    private function isList($value) : bool
    {
        $list = $value;
        $firstRow = array_pop($list);
        $keys = array_keys((array) $firstRow);
        $isMultiColumnMultiRowList = $this->isMultiColumnMultiRowList($keys, $list);
        $isMultiColumnList = $this->isMultiColumnList($value, $firstRow);
        $isSingleColumnList = $this->isSingleColumnList($value, $keys, $list);

        return $isSingleColumnList || $isMultiColumnMultiRowList || $isMultiColumnList;
    }

    private function isMultiColumnMultiRowList(array $keys, $list) : bool
    {
        return $keys !== [0 => 0] && ($keys === array_keys((array) array_pop($list)));
    }

    private function isMultiColumnList(array $value, $firstRow) : bool
    {
        return is_array($firstRow) && array_filter(array_keys($value), 'is_numeric') === array_keys($value);
    }

    private function isSingleColumnList(array $value, array $keys, array $list) : bool
    {
        return (count($value) === 1) && $keys === array_keys($list);
    }
}
