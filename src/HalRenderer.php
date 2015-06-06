<?php
/**
 * This file is part of the BEAR.Resource package
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
     */
    public function render(ResourceObject $ro)
    {
        list($ro, $body) = $this->valuate($ro);

        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = ($hasMethod) ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        /* @var $annotations Link[] */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';

        return $ro->view;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function getReverseMatchedLink($uri)
    {
        return $uri;
    }

    /**
     * @return Hal
     */
    private function getHal(Uri $uri, array $body, array $annotations)
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->getReverseMatchedLink($path);
        $hal = new Hal($selfLink, $body);
        $this->getHalLink($body, $annotations, $hal);

        return $hal;
    }

    /**
     * @param ResourceObject $ro
     *
     * @return array
     */
    private function valuate(ResourceObject $ro)
    {
        // evaluate all request in body.
        if (is_array($ro->body)) {
            $this->valuateElements($ro);
        }
        // HAL
        $body = $ro->body ?: [];
        if (is_scalar($body)) {
            $body = ['value' => $body];

            return [$ro, $body];
        }

        return[$ro, $body];
    }

    /**
     * @param \BEAR\Resource\ResourceObject $ro
     */
    private function valuateElements(ResourceObject &$ro)
    {
        foreach ($ro->body as $key => &$element) {
            if ($element instanceof RequestInterface) {
                unset($ro->body[$key]);
                $view = $this->render($element());
                $ro->body['_embedded'][$key] = json_decode($view);
            }
        }
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
