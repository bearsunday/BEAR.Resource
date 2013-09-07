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
     * Method name
     *
     * @var string
     */
    private $method;

    /**
     * Resource client
     *
     * @var ResourceInterface
     */
    private $resource;

    /**
     * Constructor
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     *
     * @Inject
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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
            $linkMethod = 'onLink' . $link->key;
            $nextResource = method_exists($current, $linkMethod) ?
                $this->methodLink($link, $current, $request) :
                $this->annotationLink($link, $current, $request);
            $current = $this->nextLink($link, $current, $nextResource);
        }

        return $current;
    }

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

    private function methodLink(LinkType $link, ResourceObject $current, Request $request)
    {
        $classMethod = 'onLink' . ucfirst($link->key);
        $args = is_array($current->body) ? $current->body : [];
        $args = $current->body;
        $nexResource =  call_user_func_array([$current, $classMethod], $args);

        return $nexResource;
    }

    private function annotationLink(LinkType $link, ResourceObject $current, Request $request)
    {
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($current, $classMethod));
        if ($link->type === LinkType::CRAWL_LINK) {
            return $this->annotationCrawl($annotations, $link, $current);
        }
        return $this->annotationRel($annotations, $link, $current)->body;
    }

    private function annotationRel(array $annotations, LinkType $link, AbstractObject $current)
    {
        foreach($annotations as $annotation)
        {
            /* @var $annotation Annotation\Link */
            if ($annotation->rel !== $link->key) {
                continue;
            }
            $linkedResource = $this
                ->resource
                ->{$annotation->method}
                ->uri($annotation->href)
                ->withQuery($current->body)
                ->eager
                ->request();
            /* @var $linkedResource AbstractObject */
            return $linkedResource;
        }
        throw new Exception\Link(get_class($current) . " link({$link->key}");
    }

    private function annotationCrawl(array $annotations, LinkType $link, AbstractObject $current)
    {
        foreach($annotations as $annotation)
        {
            /* @var $annotation Annotation\Link */
            if ($annotation->crawl !== $link->key) {
                continue;
            }
            $linkedResource = $this
                ->resource
                ->{$annotation->method}
                ->uri($annotation->href)
                ->withQuery($current->body)
                ->linkCrawl($link->key)
                ->eager
                ->request();
            /* @var $linkedResource AbstractObject */
            $current->body[$annotation->rel] = $linkedResource->body;
        }

        return $current;
    }
}
