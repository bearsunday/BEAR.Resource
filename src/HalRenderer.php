<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use function is_array;
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
        [$ro, $body] = $this->valuate($ro);
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = $hasMethod ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        /* @var $annotations Link[] */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';

        return $ro->view;
    }

    private function valuateElements(ResourceObject $ro)
    {
        foreach ($ro->body as $key => &$embeded) {
            if ($embeded instanceof AbstractRequest) {
                // @codeCoverageIgnoreStart
                if ($this->isDifferentSchema($ro, $embeded->resourceObject)) {
                    $ro->body['_embedded'][$key] = $embeded()->body;
                    unset($ro->body[$key]);

                    continue;
                }
                // @codeCoverageIgnoreEnd
                unset($ro->body[$key]);
                $view = $this->render($embeded());
                $ro->body['_embedded'][$key] = json_decode($view);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo) : bool
    {
        return $parentRo->uri->scheme . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
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
        $this->addLinks($body, $annotations, $hal);

        return $hal;
    }

    private function valuate(ResourceObject $ro) : array
    {
        $ro->body = is_array($ro->body) ? $ro->body : ['value' => $ro->body];
        // evaluate all request in body.
        $this->valuateElements($ro);

        return [$ro, $ro->body];
    }

    private function addLinks(array $body, array $annotations, Hal $hal)
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
