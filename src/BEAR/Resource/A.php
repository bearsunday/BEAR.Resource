<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use Guzzle\Parser\UriTemplate\UriTemplateInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;

/**
 * Anchor
 */
class A implements HrefInterface
{
    /**
     * @var UriTemplateInterface
     */
    protected $uriTemplate;

    /**
     * @param UriTemplateInterface $uriTemplate
     *
     * @Inject
     */
    public function __construct(UriTemplateInterface $uriTemplate)
    {
        $this->uriTemplate = $uriTemplate;
    }

    /**
     * Return hyper reference URI
     *
     * @param string         $rel
     * @param ResourceObject $ro
     *
     * @return mixed|string
     * @throws Exception\Link
     */
    public function href($rel, ResourceObject $ro)
    {
        if (!isset($ro->links[$rel])) {
            throw new Exception\Link(get_class($ro) . ':' . $rel);
        }
        $link = $ro->links[$rel];
        $isTemplated = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true);
        $uri = $isTemplated ? $this->uriTemplate->expand($link[Link::HREF], $ro->body) : $link[Link::HREF];

        return $uri;
    }
}
