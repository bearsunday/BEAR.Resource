<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Nocarrier\Hal;
use Ray\Aop\ReflectionMethod;
use RuntimeException;

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

final class HalRenderer implements RenderInterface
{
    public function __construct(
        private readonly HalLinker $linker,
    ) {
    }

    /**
     * {@inheritDoc}
     */
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

    private function valuateElements(ResourceObject $ro): void
    {
        assert(is_array($ro->body));
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
            // @codeCoverageIgnoreStart
            if ($this->isDifferentSchema($ro, $embeded->resourceObject)) {
                $ro->body['_embedded'][$key] = $embeded()->body;
                unset($ro->body[$key]);

                continue;
            }

            // @codeCoverageIgnoreEnd
            unset($ro->body[$key]);
            $view = $this->render($embeded());
            $ro->body['_embedded'][$key] = json_decode($view, null, 512, JSON_THROW_ON_ERROR);
        }
    }

    /** @codeCoverageIgnore */
    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo): bool
    {
        return $parentRo->uri->scheme . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
    }

    /**
     * @param array<array-key, mixed> $body
     * @psalm-param list<object>       $annotations
     * @phpstan-param array<object>    $annotations
     */
    private function getHal(AbstractUri $uri, array $body, array $annotations): Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->linker->getReverseLink($path, $uri->query);
        $hal = new Hal($selfLink, $body);

        return $this->linker->addHalLink($body, $annotations, $hal);
    }

    /** @return array{0: ResourceObject, 1: array<array-key, mixed>} */
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
        /** @var array<string, string> $query */

        $ro->headers['Location'] = $this->linker->getReverseLink($ro->headers['Location'], $query);
    }
}
