<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use Nocarrier\Hal;
use Ray\Aop\WeavedInterface;
use Ray\Di\Di\Inject;

class HalRenderer implements RenderInterface
{
    /**
     * @var UriMapperInterface
     */
    private $mapper;

    /**
     * @var \SplObjectStorage
     */
    private $embed;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param UriMapperInterface $mapper
     *
     * @Inject
     */
    public function __construct(UriMapperInterface $mapper, Reader $reader)
    {
        $this->mapper = $mapper;
        $this->embed = new \SplObjectStorage;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $ro = $this->pushEmbedResource($ro);
        $data = $ro->jsonSerialize();
        // HAL
        $hal = $this->getHal($ro, $data);
        $this->addEmbedResource($hal);
        $this->addLinkAnnotation($hal, $ro);
        $ro->view = $hal->asJson(true);
        $ro->headers['content-type'] = 'application/hal+json; charset=UTF-8';

        return $ro->view;
    }

    /**
     * Return HAL
     *
     * @param ResourceObject $ro
     * @param mixed          $data
     *
     * @return Hal
     * @throws Exception\HrefNotFoundException
     */
    private function getHal(ResourceObject $ro, $data)
    {
        $uri = $this->mapper->reverseMap($ro->uri);
        $hal = new Hal($uri, $data);

        return $hal;
    }

    /**
     * @param ResourceObject $ro
     *
     * @return ResourceObject
     */
    private function pushEmbedResource(ResourceObject $ro)
    {
        if (! is_array($ro->body)) {
            return $ro;
        }
        foreach ($ro->body as $rel => $request) {
            if ($request instanceof AbstractRequest) {
                $this->embed->attach($request, $rel);
                unset($ro->body[$rel]);
            }
        }

        return $ro;
    }

    /**
     * @param Hal $hal
     */
    private function addEmbedResource(Hal $hal)
    {
        foreach ($this->embed as $request) {
            $embedRel = $this->embed[$request];
            $ro = $request();
            $data = $ro->jsonSerialize();
            $uri = $this->mapper->reverseMap($ro->uri);
            $embedHal = new Hal($uri, $data);

            $links = $this->getLinks($ro);
            foreach ($links as $link) {
                if ($link instanceof Link) {
                    $mappedLink = $this->mapper->reverseMap($link->href);
                    $embedHal->addLink($link->rel, $mappedLink);
                }
            }

            $hal->addResource($embedRel, $embedHal);
        }
    }

    private function addLinkAnnotation(Hal $hal, ResourceObject $ro)
    {
        $this->getLinks($ro);
        $links = $this->getLinks($ro);
        foreach ($links as $link) {
            if ($link instanceof Link) {
                $mappedLink = $this->mapper->reverseMap($link->href);
                $hal->addLink($link->rel, $mappedLink);
            }
        }

//        $hal->addResource($ro, $hal);
    }

    /**
     * @param ResourceObject $ro
     *
     * @return array|Link[]
     */
    private function getLinks(ResourceObject $ro)
    {
        $object = ($ro instanceof WeavedInterface) ? (new \ReflectionClass($ro))->getParentClass()->name : $ro;
        $links = method_exists($object, 'onGet') ? $this->reader->getMethodAnnotations(new \ReflectionMethod($object, 'onGet')) : [];

        return (array) $links;
    }
}
