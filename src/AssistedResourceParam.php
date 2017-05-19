<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Ray\Di\InjectorInterface;

final class AssistedResourceParam implements ParamInterface
{
    private $resourceParam;

    public function __construct(ResourceParam $resourceParam)
    {
        $this->resourceParam = $resourceParam;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        $resource = $injector->getInstance(ResourceInterface::class);
        $uri = $this->resourceParam->templated === true ? uri_template($this->resourceParam->uri, $query) : $this->resourceParam->uri;
        $resourceResult = $resource->get->uri($uri)->eager->request();
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);

        return $resourceResult[$fragment];
    }
}
