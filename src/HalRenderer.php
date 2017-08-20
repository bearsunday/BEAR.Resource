<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use Nocarrier\Hal;

class HalRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function render(ResourceObject $ro)
    {
        list($ro, $body) = $this->valuate($ro);

        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = $hasMethod ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        /* @var $annotations Link[] */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';

        return $ro->view;
    }

    private function getReverseMatchedLink(string $uri) : string
    {
        return $uri;
    }

    private function getHal(AbstractUri $uri, array $body, array $annotations) : Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->getReverseMatchedLink($path);
        $hal = new Hal($selfLink, $body);
        $this->getHalLink($body, $annotations, $hal);

        return $hal;
    }

    private function valuate(ResourceObject $ro)
    {
        // HAL
        $body = $ro->body ?: [];

        return [$ro, $body];
    }

    private function getHalLink(array $body, array $annotations, Hal $hal)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $uri = uri_template($annotation->href, $body);
                $reverseUri = $this->getReverseMatchedLink($uri);
                $hal->addLink($annotation->rel, $reverseUri);
            }
        }
    }
}
