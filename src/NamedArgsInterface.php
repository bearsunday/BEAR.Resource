<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\MethodInvocation;

/**
 * Interface for named parameter in interceptor
 */
interface NamedArgsInterface
{
    /**
     * @param MethodInvocation $invocation
     *
     * @return array
     */
    public function get(MethodInvocation $invocation);
}
