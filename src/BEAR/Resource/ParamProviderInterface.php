<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for ParamProvider
 */
interface ParamProviderInterface
{
    /**
     * @param Param $param
     *
     * @return mixed
     */
    public function __invoke(Param $param);
}
