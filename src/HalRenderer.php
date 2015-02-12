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

class HalRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Uri
     */
    private $uri;

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
        if (! $hasMethod) {
            $ro->view = ''; // options has no view

            return '';
        }
        $links = ($hasMethod) ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method), Link::class) : [];
        /** @var $links Link[] */
        $hal = $this->getHal($ro->uri, $body, $links);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';
        
        return $ro->view;
    }

    /**
     * @param string $uri
     *
     * @return mixed
     */
    protected function getReverseMatchedLink($uri)
    {
        return $uri;
    }

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

    /**
     * @return Hal
     */
    private function getHal(Uri $uri, array $body, array $links)
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->getReverseMatchedLink($path);
        $hal = new Hal($selfLink, $body);
        $this->getHalLink($body, $links, $hal);

        return $hal;
    }

    /**
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

            return array($ro, $body);
        }

        return[$ro, $body];
    }

    private function getHalLink(array $body, array $links, Hal $hal)
    {
        foreach ($links as $link) {
            if (!$link instanceof Link) {
                continue;
            }
            $uri = uri_template($link->href, $body);
            $reverseUri = $this->getReverseMatchedLink($uri);
            $hal->addLink($link->rel, $reverseUri);
        }
    }
}
