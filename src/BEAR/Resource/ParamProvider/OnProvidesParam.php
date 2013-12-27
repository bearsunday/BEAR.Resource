<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\ParamProvider;

use BEAR\Resource\ParamInterface;
use BEAR\Resource\ParamProviderInterface;

/**
 * Provides parameter inline 'onProvides' method
 *
 * - 'onProvidesLoginId'method enable to provides $login_id or $loginId parameter in same class.
 */
class OnProvidesParam implements ParamProviderInterface
{
    /**
     * @param ParamInterface $param
     *
     * @return mixed
     */
    public function __invoke(ParamInterface $param)
    {
        $provideMethod = 'onProvides' . ucfirst(str_replace('_', '', $param->getParameter()->name));
        $object = $param->getMethodInvocation()->getThis();
        if (method_exists($object, $provideMethod)) {
            $arg = $object->{$provideMethod}();

            return $param->inject($arg);
        }

        return null;
    }
}
