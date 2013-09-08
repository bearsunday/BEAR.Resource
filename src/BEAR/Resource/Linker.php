<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Exception;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Guzzle\Parser\UriTemplate\UriTemplate;
use Guzzle\Parser\UriTemplate\UriTemplateInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
use ReflectionMethod;
use Ray\Di\Di\Inject;

/**
 * Resource linker
 *
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
     * @var \Guzzle\Parser\UriTemplate\UriTemplate
     */
    private $uriTemplate;

    /**
     * @param Reader               $reader
     * @param Cache                $cache
     * @param UriTemplateInterface $uriTemplate
     *
     * @Inject
     */
    public function __construct(
        Reader $reader,
        Cache $cache = null,
        UriTemplateInterface $uriTemplate = null
    ) {
        $this->reader = $reader;
        $this->cache = $cache ?: new ArrayCache;
        $this->uriTemplate = $uriTemplate ?: new UriTemplate;
    }

    /**
     * Set resource
     *
     * @param $resource $resource
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
     * @param AbstractObject $ro
     * @param $nextResource
     *
     * @return AbstractObject
     */
    private function nextLink(LinkType $link, ResourceObject $ro, $nextResource)
    {
        $nextBody = $nextResource instanceof AbstractObject ? $nextResource->body : $nextResource;

        if ($link->type === LinkType::SELF_LINK) {
            $ro->body = $nextBody;
            return $ro;
        }

        if ($link->type === LinkType::NEW_LINK) {
            $ro->body = [$ro->body, $nextBody];
            return $ro;
        }
        // crawl
        return $ro;
    }

    /**
     * Annotation link
     *
     * @param LinkType       $link
     * @param AbstractObject $current
     * @param Request        $request
     *
     * @return AbstractObject|mixed
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
     * @param AbstractObject $current
     *
     * @return AbstractObject
     * @throws Exception\LinkRel
     */
    private function annotationRel(array $annotations, LinkType $link, AbstractObject $current)
    {
        foreach ($annotations as $annotation) {
            /* @var $annotation Annotation\Link */
            if ($annotation->rel !== $link->key) {
                continue;
            }
            $uri = $this->uriTemplate->expand($annotation->href, $current->body);
            $linkedResource = $this
                ->resource
                ->{$annotation->method}
                ->uri($uri)
                ->eager
                ->request();
            /* @var $linkedResource AbstractObject */
            return $linkedResource;
        }
        throw new Exception\LinkRel("[{$link->key}] in " . get_class($current) . ' is not available.');
    }

    /**
     * Link annotation crawl
     *
     * @param array          $annotations
     * @param LinkType       $link
     * @param AbstractObject $current
     *
     * @return AbstractObject
     */
    private function annotationCrawl(array $annotations, LinkType $link, AbstractObject $current)
    {
        $isList = $this->isList($current->body);
        $bodyList = $isList ? $current->body : [ $current->body];
        foreach ($bodyList as &$body) {
            foreach ($annotations as $annotation) {
                /* @var $annotation Annotation\Link */
                if ($annotation->crawl !== $link->key) {
                    continue;
                }
                $uri = $this->uriTemplate->expand($annotation->href, $body);
                $request = $this
                    ->resource
                    ->{$annotation->method}
                    ->uri($uri)
                    ->linkCrawl($link->key)
                    ->request();
                /* @var $request Request */
                $hash = $request->hash();
                if ($this->cache->contains($hash)) {
                    $body[$annotation->rel] =$this->cache->fetch($hash);
                    continue;
                }
                /* @var $linkedResource AbstractObject */
                $body[$annotation->rel] = $request()->body;
                $this->cache->save($hash, $body[$annotation->rel]);
            }
        }
        $current->body = $isList ? $bodyList : $bodyList[0];
        return $current;
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
        $value = array_values((array)$value);
        $isMultiColumnList = (count($value) > 1
            && isset($value[0])
            && isset($value[1])
            && is_array($value[0])
            && is_array($value[1])
            && (array_keys($value[0]) === array_keys($value[1])));
        $isOneColumnList = (count($value) === 1) && is_array($value[0]);

        return ($isOneColumnList | $isMultiColumnList);
    }
}
