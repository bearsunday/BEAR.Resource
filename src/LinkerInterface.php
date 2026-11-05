<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface LinkerInterface
{
    /**
     * InvokerInterface link
     *
     * @return ResourceObject
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function invoke(AbstractRequest $request);
}
