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
use Nocarrier\Hal;

class HalRenderer implements RenderInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $embed;

    public function __construct()
    {
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
     * @param                $data
     *
     * @return Hal
     * @throws Exception\HrefNotFound
     */
    private function getHal(ResourceObject $ro, $data)
    {
        $hal = new Hal($ro->uri, $data);
        foreach ($ro->links as $rel => $link) {
            $attr = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true) ? [Link::TEMPLATED => true] : [];
            if (!isset($link[Link::HREF])) {
                throw new Exception\HrefNotFound($rel);
            }
            $hal->addLink($rel, $link[Link::HREF], $attr);
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
            $rel = $this->embed[$request];
            $ro = $request();
            $data = $ro->jsonSerialize();
            $embedHal = new Hal($ro->uri, $data);
            $hal->addResource($rel, $embedHal);
        }
    }
}
