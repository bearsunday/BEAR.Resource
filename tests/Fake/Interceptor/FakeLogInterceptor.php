<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class FakeLogInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = $invocation->getThis()::class;
        $input = (array) $invocation->getArguments();
        $input = print_r($input, true);
        $class = get_parent_class($class);
        $result .= "[Log] target = $class, input = $input, result = $result";

        return $result;
    }
}
