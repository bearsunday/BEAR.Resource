<?php
namespace BEAR\Resource\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * Log Interceptor
 */
class Log implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = get_class($invocation->getThis());
        $input = (array) $invocation->getArguments();
        $input = print_r($input, true);
        $class = get_parent_class($class);
        $result .= "[Log] target = $class, input = $input, result = $result";

        return $result;
    }
}
