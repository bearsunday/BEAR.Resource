<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\MethodInvocation;
use Ray\Aop\Weave;

/**
 * Resource request invoke interface
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Invoker")
 */
interface NamedParamInterface
{
    /**
     * Return parameters
     *
     * @param MethodInvocation $invocation
     * @param Weave            $weave
     *
     * @return mixed
     */
    public function invoke(MethodInvocation $invocation, Weave $weave = null);

    /**
     * Attach parameter provider
     *
     * @param string                 $varName
     * @param ParamProviderInterface $provider
     *
     * @return self
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider);
}
