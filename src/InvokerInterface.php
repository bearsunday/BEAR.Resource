<?php declare(strict_types=1);
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
     */
    public function invoke(AbstractRequest $request) : ResourceObject;
}
