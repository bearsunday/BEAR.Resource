<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface InvokerInterface
{
    /**
     * Invoke resource request
     *
     * @param Request $request
     *
     * @return ResourceObject
     */
    public function invoke(AbstractRequest $request);
}
