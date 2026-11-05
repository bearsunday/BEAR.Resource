<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @psalm-import-type MethodUri from Types
 * @psalm-import-type Query from Types
 */
interface AnchorInterface
{
    /**
     * Return linked request with hyper reference
     *
     * @param string          $rel     Relation
     * @param AbstractRequest $request Resource request
     * @param Query           $query   Resource parameters
     *
     * @return MethodUri [$method, $uri]
     */
    public function href(string $rel, AbstractRequest $request, array $query);
}
