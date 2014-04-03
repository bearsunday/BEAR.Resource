<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface ParamProviderInterface
{
    /**
     * @param ParamInterface $param
     *
     * @return string|null
     */
    public function __invoke(ParamInterface $param);
}
