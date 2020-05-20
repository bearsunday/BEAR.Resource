<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use function is_array;
use function is_scalar;
use Nocarrier\Hal;

class HalRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var HalLink
     */
    private $link;

    public function __construct(Reader $reader, HalLink $link)
    {
        $this->reader = $reader;
        $this->link = $link;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $this->renderHal($ro);
        $this->updateHeaders($ro);

        return (string) $ro->view;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function renderHal(ResourceObject $ro) : void
    {
        [$ro, $body] = $this->valuate($ro);
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = $hasMethod ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        /* @var $annotations Link[] */
        $hal = $this->getHal($ro->uri, (array) $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';
    }

    private function valuateElements(ResourceObject $ro) : void
    {
        if (! is_array($ro->body)) {
            return;
        }
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

    /**
     * @param array<string, mixed> $body
     * @param array<int, object>   $annotations
     */
    private function getHal(AbstractUri $uri, array $body, array $annotations) : Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->link->getReverseLink($path);
        $hal = new Hal($selfLink, $body);

        return $this->link->addHalLink($body, $annotations, $hal);
    }

    /**
     * @return array{0: ResourceObject, 1: array<string, mixed>}
     */
    private function valuate(ResourceObject $ro) : array
    {
        if (is_scalar($ro->body) && $ro->body !== null) {
            $ro->body = ['value' => $ro->body];
        }
        // evaluate all request in body.
        $this->valuateElements($ro);

        return [$ro, $ro->body];
    }

    private function updateHeaders(ResourceObject $ro) : void
    {
        $ro->headers['Content-Type'] = 'application/hal+json';
        if (isset($ro->headers['Location'])) {
            $ro->headers['Location'] = $this->link->getReverseLink($ro->headers['Location']);
        }
    }
}
