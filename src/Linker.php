<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use ReflectionMethod;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;

/**
 * Resource linker
 *
 * @Scope("singleton")
 */
final class Linker implements LinkerInterface
{
    /**
     * Resource client
     *
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Reader $reader
     * @param Cache  $cache
     *
     * @Inject
     */
    public function __construct(
        Reader $reader,
        Cache $cache = null
    ) {
        $this->reader = $reader;
        $this->cache = $cache ? : new ArrayCache;
    }

    /**
     * {@inheritdoc}
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function invoke(Request $request)
    {
        $current = clone $request->ro;
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
     * @param                $nextResource
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
     * @param LinkType       $link
     * @param ResourceObject $current
     * @param Request        $request
     *
     * @return ResourceObject|mixed
     * @throws Exception\LinkQuery
     */
    private function annotationLink(LinkType $link, ResourceObject $current, Request $request)
    {
        if (!(is_array($current->body))) {
            throw new Exception\LinkQuery('Only array is allowed for link in ' . get_class($current));
        }
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($current, $classMethod));
        if ($link->type === LinkType::CRAWL_LINK) {
            return $this->annotationCrawl($annotations, $link, $current);
        }

        return $this->annotationRel($annotations, $link, $current)->body;
    }

    /**
     * Annotation link (new, self)
     *
     * @param array          $annotations
     * @param LinkType       $link
     * @param ResourceObject $current
     *
     * @return ResourceObject
     * @throws Exception\LinkQuery
     * @throws Exception\LinkRel
     */
    private function annotationRel(array $annotations, LinkType $link, ResourceObject $current)
    {
        foreach ($annotations as $annotation) {
            /* @var $annotation Annotation\Link */
            if ($annotation->rel !== $link->key) {
                continue;
            }
            $uri = \GuzzleHttp\uri_template($annotation->href, $current->body);
            try {
                $linkedResource = $this->resource->{$annotation->method}->uri($uri)->eager->request();
                /* @var $linkedResource ResourceObject */
            } catch (Exception\Parameter $e) {
                $msg = 'class:' . get_class($current) . " link:{$link->key} query:" . json_encode($current->body);
                throw new Exception\LinkQuery($msg, 0, $e);
            }

            return $linkedResource;
        }
        throw new Exception\LinkRel("[{$link->key}] in " . get_class($current) . ' is not available.');
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
     * @param array    $annotations
     * @param LinkType $link
     * @param array    $body
     */
    private function crawl(array $annotations, LinkType $link, array &$body)
    {
        foreach ($annotations as $annotation) {
            /* @var $annotation Annotation\Link */
            if ($annotation->crawl !== $link->key) {
                continue;
            }
            $uri = \GuzzleHttp\uri_template($annotation->href, $body);
            $request = $this->resource->{$annotation->method}->uri($uri)->linkCrawl($link->key)->request();
            /* @var $request Request */
            $hash = $request->hash();
            if ($this->cache->contains($hash)) {
                $body[$annotation->rel] = $this->cache->fetch($hash);
                continue;
            }
            /* @var $linkedResource ResourceObject */
            $body[$annotation->rel] = $request()->body;
            $this->cache->save($hash, $body[$annotation->rel]);
        }
    }

    /**
     * Is data list ?
     *
     * @param mixed $value
     *
     * @return boolean
     */
    private function isList($value)
    {
        $value = array_values((array) $value);
        $isMultiColumnList = (count($value) > 1
            && isset($value[0])
            && isset($value[1])
            && is_array($value[0])
            && is_array($value[1])
            && (array_keys($value[0]) === array_keys($value[1]))
        );
        $isOneColumnList = (count($value) === 1) && is_array($value[0]);

        return ($isOneColumnList | $isMultiColumnList);
    }
}
