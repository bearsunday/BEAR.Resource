<?php
namespace testworld\Interceptor;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

/**
 * Log Interceptor
 *
 */
class Log implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = get_class($invocation->getThis());
        $input = $invocation->getArguments();
        $input = print_r($input, true);
        $result .= "[Log] target = $class, input = $input, result = $result";
        return $result;
    }
}