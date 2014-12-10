<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkRelException;
use Doctrine\Common\Annotations\Reader;

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
     * @param Reader           $reader
     * @param InvokerInterface $invoker
     * @param FactoryInterface $factory
     */
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
     * {@inheritDoc}
     */
    public function invoke(AbstractRequest $request)
    {
        $this->invoker->invoke($request);
        $current = clone $request->resourceObject;
        foreach ($request->links as $link) {
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
     *
     * @return ResourceObject
     */
    private function nextLink(LinkType $link, ResourceObject $ro, $nextResource)
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
     * @param LinkType        $link
     * @param ResourceObject  $current
     * @param AbstractRequest $request
     *
     * @return ResourceObject|mixed
     * @throws Exception\LinkQueryException
     */
    private function annotationLink(LinkType $link, ResourceObject $current, AbstractRequest $request)
    {
        if (!(is_array($current->body))) {
            throw new Exception\LinkQueryException('Only array is allowed for link in ' . get_class($current));
        }
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($current, $classMethod));
        if ($link->type === LinkType::CRAWL_LINK) {
            return $this->annotationCrawl($annotations, $link, $current);
        }

        return $this->annotationRel($annotations, $link, $current)->body;
    }

    /**
     * Annotation link (new, self)
     *
     * @param \BEAR\Resource\Annotation\Link[] $annotations
     * @param LinkType                         $link
     * @param ResourceObject                   $current
     *
     * @return ResourceObject
     * @throws Exception\LinkQueryException
     * @throws Exception\LinkRelException
     */
    private function annotationRel(array $annotations, LinkType $link, ResourceObject $current)
    {
        foreach ($annotations as $annotation) {
            if ($annotation->rel !== $link->key) {
                continue;
            }
            $uri = uri_template($annotation->href, $current->body);
            $rel = $this->factory->newInstance($uri);
            $request = new Request($this->invoker, $rel, Request::GET, (new Uri($uri))->query);
            $linkedResource = $this->invoker->invoke($request);

            return $linkedResource;
        }
        throw new LinkRelException("rel:{$link->key} class:" . get_class($current));
    }

    /**
     * Link annotation crawl
     *
     * @param array          $annotations
     * @param LinkType       $link
     * @param ResourceObject $current
     *
     * @return ResourceObject
     */
    private function annotationCrawl(array $annotations, LinkType $link, ResourceObject $current)
    {
        $isList = $this->isList($current->body);
        $bodyList = $isList ? $current->body : [$current->body];
        /** @var $bodyList array */
        foreach ($bodyList as &$body) {
            $this->crawl($annotations, $link, $body);
        }
        $current->body = $isList ? $bodyList : $bodyList[0];

        return $current;
    }

    /**
     * @param Link[]   $annotations
     * @param LinkType $link
     * @param array    $body
     */
    private function crawl(array $annotations, LinkType $link, array &$body)
    {
        foreach ($annotations as $annotation) {
            if ($annotation->crawl !== $link->key) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $rel = $this->factory->newInstance($uri);
            $request = new Request($this->invoker, $rel, Request::GET, (new Uri($uri))->query, [$link]);
            $hash = $request->hash();
            $body[$annotation->rel] = $this->invoke($request)->body;
            $this->cache[$hash] = $body[$annotation->rel];
        }
    }

    /**
     * @param mixed $value
     *
     * @return boolean
     */
    private function isList($value)
    {
        $list = $value;
        $keys = array_keys((array) array_pop($list));
        $isMultiColumnList = $keys !== [0 => 0] && ($keys === array_keys((array) array_pop($list)));
        $isOneColumnList = (count($value) === 1) && is_array($value[0]);

        return ($isOneColumnList || $isMultiColumnList);
    }
}
