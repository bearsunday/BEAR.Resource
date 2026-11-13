<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\Exception\UriException;
use Override;
use Ray\Di\InjectorInterface;

use function explode;
use function sprintf;
use function str_contains;
use function uri_template;

final readonly class AssistedResourceParam implements ParamInterface
{
    public function __construct(
        private ResourceParam $resourceParam,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($varName);
        $resource = $injector->getInstance(ResourceInterface::class);
        $uri = $this->resourceParam->templated === true ? uri_template($this->resourceParam->uri, $query) : $this->resourceParam->uri;
        // The fragment names a key of the body, not a part of the request
        [$requestUri, $key] = explode('#', $uri, 2) + [1 => ''];
        if (str_contains($key, '=')) {
            throw new UriException(sprintf('%s: a #[ResourceParam] fragment names a key of the body; a link (name=value) is not supported', $uri), 500);
        }

        $resourceResult = $resource->newRequest(Method::GET, $requestUri)();

        return $resourceResult[$key];
    }
}
