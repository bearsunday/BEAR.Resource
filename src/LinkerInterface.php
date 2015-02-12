<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface LinkerInterface
{
    /**
     * InvokerInterface link
     *
     * @param AbstractRequest $request
     *
     * @return mixed
     */
    public function invoke(AbstractRequest $request);
}
