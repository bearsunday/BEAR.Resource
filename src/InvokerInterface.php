<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface InvokerInterface
{
    /**
     * Invoke resource request
     */
    public function invoke(AbstractRequest $request) : ResourceObject;
}
