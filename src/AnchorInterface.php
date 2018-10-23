<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface AnchorInterface
{
    /**
     * Return linked request with hyper reference
     *
     * @param string          $rel     Realaction
     * @param AbstractRequest $request Resource request
     * @param array           $query   Resource parameters
     */
    public function href(string $rel, AbstractRequest $request, array $query);
}
