<?php
/**
 * This file is part of the BEAR.Sunday package.
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
     * @return mixed
     */
    public function invoke(AbstractRequest $request);
}
