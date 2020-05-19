<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface AnchorInterface
{
    /**
     * Return linked request with hyper reference
     *
     * @param string               $rel     Relation
     * @param AbstractRequest      $request Resource request
     * @param array<string, mixed> $query   Resource parameters
     *
     * @return array{0:string, 1:string} [$method, $uri]
     */
    public function href(string $rel, AbstractRequest $request, array $query);
}
