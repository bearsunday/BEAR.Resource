<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface InvokerInterface
{
    /**
     * Invoke resource request
     *
     * @param AbstractRequest $request
     *
     * @return ResourceObject
     */
    public function invoke(AbstractRequest $request);
}
