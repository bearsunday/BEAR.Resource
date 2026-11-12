<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Nocarrier\Hal;
use Override;
use Ray\Aop\ReflectionMethod;
use RuntimeException;

use function array_replace;
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
 */
final readonly class HalRenderer implements RenderInterface
{
    public function __construct(
        private HalLinker $linker,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-taint-escape html
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
     */
    public function renderHal(ResourceObject $ro): void
    {
        $body = $this->valuate($ro);
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        $annotations = $hasMethod ? (new ReflectionMethod($ro, $method))->getAnnotations() : [];
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $json = $hal->asJson(true);
        assert(is_string($json));
        $ro->view = $json . PHP_EOL;
        $ro->headers['Content-Type'] = 'application/hal+json';
    }

    /**
     * @param array<array-key, mixed> $body
     *
     * @return array<array-key, mixed>
     */
    private function valuateElements(ResourceObject $ro, array $body): array
    {
        // Batch decorators (e.g. BEAR.Async's AsyncRequest) flush the whole
        // pending batch on the first __toString(), so siblings must already
        // have HalRenderer set or they fall back to JsonRenderer and lose _links.
        foreach ($body as $maybeRequest) {
            if (! ($maybeRequest instanceof AbstractRequest)) {
                continue;
            }

            if ($this->isDifferentSchema($ro, $maybeRequest->resourceObject)) {
                continue;
            }

            $maybeRequest->resourceObject->setRenderer($this);
        }

        $results = [];
        /** @var mixed $embeded */
        foreach ($body as $key => $embeded) {
            if (! ($embeded instanceof AbstractRequest)) {
                continue;
            }

            unset($body[$key]);
            /** @psalm-suppress MixedAssignment */
            $results[$key] = $this->embed($ro, $embeded);
        }

        if ($results === []) {
            return $body;
        }

        $existing = isset($body['_embedded']) && is_array($body['_embedded']) ? $body['_embedded'] : [];
        $body['_embedded'] = array_replace($existing, $results);

        return $body;
    }

    private function embed(ResourceObject $ro, AbstractRequest $request): mixed
    {
        if ($this->isDifferentSchema($ro, $request->resourceObject)) {
            return $request->body;
        }

        // Use (string) so lazy decorators can short-circuit __invoke().
        return json_decode((string) $request, null, 512, JSON_THROW_ON_ERROR);
    }

    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo): bool
    {
        return $parentRo->uri->scheme . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
    }

    /**
     * @param Body          $body
     * @param array<object> $annotations
     */
    private function getHal(AbstractUri $uri, array $body, array $annotations): Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->linker->getReverseLink($path, $uri->query);
        $hal = new Hal($selfLink, $body);

        return $this->linker->addHalLink($body, array_values($annotations), $hal);
    }

    /** @return array<array-key, mixed> */
    private function valuate(ResourceObject $ro): array
    {
        /** @var mixed $body */
        $body = $ro->body;
        if (is_scalar($body)) {
            $body = ['value' => $body];
        }

        if ($body === null) {
            $body = [];
        }

        if (is_object($body)) {
            $body = (array) $body;
        }

        assert(is_array($body));

        return $this->valuateElements($ro, $body);
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
        /** @var Query $query */

        $ro->headers['Location'] = $this->linker->getReverseLink($ro->headers['Location'], $query);
    }
}
