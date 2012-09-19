<?php
/**
 * BEAR.Resource;
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Exception\InvalidLink;
use Guzzle\Parser\UriTemplate\UriTemplateInterface;

/**
 * Get hyper reffernce
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class A implements Referable
{
    /**
     * Constructor
     *
     * @param UriTemplate $uriTemplate
     *
     * @Inject
     */
    public function __construct(UriTemplateInterface $uriTemplate)
    {
        $this->uriTemplate = $uriTemplate;
    }

    /**
     * Get hyper reference URI
     *
     * @param string         $rel
     * @param ResourceObject $ro
     */
    public function href($rel, ResourceObject $ro)
    {
        if (! isset($ro->links[$rel])) {
            throw new InvalidLink(get_class($ro) . ':' . $rel);
        }
        $link = $ro->links[$rel];
        $isTemplated = (isset($link[Link::TEMPLATED]) &&  $link[Link::TEMPLATED] === true);
        $uri = $isTemplated ? $this->uriTemplate->expand($link[Link::HREF], $ro->body) : $link[Link::HREF];

        return $uri;
    }
}
