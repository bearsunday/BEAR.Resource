<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface AnchorInterface
{
    /**
     * Return linked request with hyper reference
     *
     * @param string          $rel     Realaction
     * @param AbstractRequest $request Resource request
     * @param array           $query   Resource parameters
     *
     * @return mixed
     */
    public function href($rel, AbstractRequest $request, array $query);
}
