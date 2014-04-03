<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Renderer;

use BEAR\Resource\Exception;
use BEAR\Resource\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceObject;
use Nocarrier\Hal;

class HalRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $data = $ro->jsonSerialize();
        // HAL
        $hal = $this->getHal($ro, $data);
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
}
