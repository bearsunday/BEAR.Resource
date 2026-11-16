<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\ResourceLayoutInterceptorInterface;
use Ray\Aop\MethodInvocation;

final class FakeResourceLayoutInterceptor implements ResourceLayoutInterceptorInterface
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->getThis()->body = ['swapped' => true];

        return $invocation->proceed();
    }
}
