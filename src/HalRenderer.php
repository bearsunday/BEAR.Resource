<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Nocarrier\Hal;
use Override;
use Ray\Aop\ReflectionMethod;
use RuntimeException;

use function array_values;
use function assert;
use function http_build_query;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function json_decode;
use function method_exists;
use function parse_str;
use function parse_url;
use function ucfirst;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;
use const PHP_URL_QUERY;

/**
 * @psalm-import-type Body from Types
 * @psalm-import-type Query from Types
 * @psalm-import-type ResourceObjectBody from Types
 */
final readonly class HalRenderer implements RenderInterface
{
    /** @psalm-pure */
    public function __construct(
        private HalLinker $linker,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-taint-escape html
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function render(ResourceObject $ro)
    {
        $this->renderHal($ro);
        $this->updateHeaders($ro);

        return (string) $ro->view;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function renderHal(ResourceObject $ro): void
    {
        [$ro, $body] = $this->valuate($ro);
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = $hasMethod ? (new ReflectionMethod($ro, $method))->getAnnotations() : [];
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $json = $hal->asJson(true);
        assert(is_string($json));
        $ro->view = $json . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';
    }

    /** @psalm-impure */
    private function valuateElements(ResourceObject $ro): void
    {
        assert(is_array($ro->body));

        // Batch decorators (e.g. BEAR.Async's AsyncRequest) flush the whole
        // pending batch on the first __toString(), so siblings must already
        // have HalRenderer set or they fall back to JsonRenderer and lose _links.
        foreach ($ro->body as $maybeRequest) {
            if (! ($maybeRequest instanceof AbstractRequest)) {
                continue;
            }

            if ($this->isDifferentSchema($ro, $maybeRequest->resourceObject)) {
                continue;
            }

            $maybeRequest->resourceObject->setRenderer($this);
        }

        /** @var mixed $embeded */
        foreach ($ro->body as $key => &$embeded) {
            if (! ($embeded instanceof AbstractRequest)) {
                continue;
            }

            $isNotArray = ! isset($ro->body['_embedded']) || ! is_array($ro->body['_embedded']);
            if ($isNotArray) {
                $ro->body['_embedded'] = [];
            }

            assert(is_array($ro->body['_embedded']));
            if ($this->isDifferentSchema($ro, $embeded->resourceObject)) {
                $ro->body['_embedded'][$key] = $embeded()->body;
                unset($ro->body[$key]);

                continue;
            }

            unset($ro->body[$key]);
            // Use (string) so lazy decorators can short-circuit __invoke().
            $view = (string) $embeded;
            $ro->body['_embedded'][$key] = json_decode($view, null, 512, JSON_THROW_ON_ERROR);
        }
    }

    /** @psalm-capabilities read-props */
    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo): bool
    {
        return $parentRo->uri->scheme . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
    }

    /**
     * @param Body          $body
     * @param array<object> $annotations
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    private function getHal(AbstractUri $uri, array $body, array $annotations): Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->linker->getReverseLink($path, $uri->query);
        $hal = new Hal($selfLink, $body);

        return $this->linker->addHalLink($body, array_values($annotations), $hal);
    }

    /**
     * @return ResourceObjectBody
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function valuate(ResourceObject $ro): array
    {
        if (is_scalar($ro->body)) {
            $ro->body = ['value' => $ro->body];
        }

        if ($ro->body === null) {
            $ro->body = [];
        }

        if (is_object($ro->body)) {
            $ro->body = (array) $ro->body;
        }

        // evaluate all request in body.
        $this->valuateElements($ro);
        assert(is_array($ro->body));

        return [$ro, $ro->body];
    }

    /** @psalm-impure */
    private function updateHeaders(ResourceObject $ro): void
    {
        $ro->headers['Content-Type'] = 'application/hal+json';
        if (! isset($ro->headers['Location'])) {
            return;
        }

        $url = parse_url($ro->headers['Location'], PHP_URL_QUERY);
        $isRelativePath = $url === null;
        $path = $isRelativePath ? $ro->headers['Location'] : $url;
        parse_str((string) $path, $query);
        /** @var Query $query */

        $ro->headers['Location'] = $this->linker->getReverseLink($ro->headers['Location'], $query);
    }
}
