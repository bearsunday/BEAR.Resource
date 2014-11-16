<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Anchor
 */
class A implements HrefInterface
{
    /**
     * Return hyper reference URI
     *
     * @param string         $rel
     * @param ResourceObject $ro
     *
     * @return mixed|string
     * @throws Exception\LinkException
     */
    public function href($rel, ResourceObject $ro)
    {
        if (!isset($ro->links[$rel])) {
            throw new Exception\LinkException(get_class($ro) . ':' . $rel);
        }
        $link = $ro->links[$rel];
        $isTemplated = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true);
        $uri = $isTemplated ? \GuzzleHttp\uri_template($link[Link::HREF], $ro->body) : $link[Link::HREF];

        return $uri;
    }
}
