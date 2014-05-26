<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Renderer;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\Exception;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\UriMapperInterface;
use Nocarrier\Hal;
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
     * @param UriMapperInterface $mapper
     *
     * @Inject
     */
    public function __construct(UriMapperInterface $mapper)
    {
        $this->mapper = $mapper;
        $this->embed = new \SplObjectStorage;
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
     * @throws Exception\HrefNotFound
     */
    private function getHal(ResourceObject $ro, $data)
    {
        $uri = $this->mapper->reverseMap($ro->uri);
        $hal = new Hal($uri, $data);
        foreach ($ro->links as $rel => $link) {
            $attr = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true) ? [Link::TEMPLATED => true] : [];
            if (!isset($link[Link::HREF])) {
                throw new Exception\HrefNotFound($rel);
            }
            $link = $this->mapper->reverseMap($link[Link::HREF]);
            $hal->addLink($rel, $link, $attr);
        }

        return $hal;
    }

    /**
     * @param ResourceObject $ro
     *
     * @return ResourceObject
     */
    private function pushEmbedResource(ResourceObject $ro)
    {
        if (is_scalar($ro->body)) {
            return $ro;
        }
        foreach ($ro->body as $rel => $request) {
            if ($request instanceof AbstractRequest) {
                $this->embed->attach($request, $rel);
                if (isset($ro->body[$rel])) {
                    unset($ro->body[$rel]);
                }
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
            foreach ($ro->links as $rel => $link) {
                $mappedLink = $this->mapper->reverseMap($link);
                $embedHal->addLink($rel, $mappedLink);
            }
            $hal->addResource($embedRel, $embedHal);
        }
    }
}
